<?php
/**
 * @package plugins.youTubeDistribution
 * @subpackage lib
 */
class vYouTubeDistributionEventConsumer implements vBatchJobStatusEventConsumer
{
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		$jobTypes = array(
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE),
		);
		if(in_array($dbBatchJob->getJobType(), $jobTypes))
			return true;
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{
		self::onDistributionJobUpdated($dbBatchJob, $dbBatchJob->getData());
			
		return true;
	}

	/**
	 * @param BatchJob $dbBatchJob
	 * @param vDistributionJobData $data
	 * @return BatchJob
	 */
	public static function onDistributionJobUpdated(BatchJob $dbBatchJob, vDistributionJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_ALMOST_DONE:
				return self::onDistributionJobUpdatedAlmostDone($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return self::onDistributionJobFinished($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	/**
	 * @param BatchJob $dbBatchJob
	 * @param vDistributionJobData $data
	 * @return BatchJob
	 */
	public static function onDistributionJobUpdatedAlmostDone(BatchJob $dbBatchJob, vDistributionJobData $data)
	{
		$entryDistribution = EntryDistributionPeer::retrieveByPK($data->getEntryDistributionId());
		if(!$entryDistribution)
		{
			VidiunLog::err("Entry distribution [" . $data->getEntryDistributionId() . "] not found");
			return $dbBatchJob;
		}

		$distributionProfileId = $data->getDistributionProfileId();
		$distributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);

		// only feed spec v1 (legacy) is setting the playlists on submit action
		if ($distributionProfile &&
			$distributionProfile instanceof YouTubeDistributionProfile &&
			$distributionProfile->getFeedSpecVersion() == YouTubeDistributionFeedSpecVersion::VERSION_1)
		{
			self::saveCurrentPlaylistsToCustomData($data, $entryDistribution);
		}
		
		return $dbBatchJob;
	}

	/**
	 * @param BatchJob $dbBatchJob
	 * @param vDistributionJobData $data
	 * @return BatchJob
	 */
	public static function onDistributionJobFinished(BatchJob $dbBatchJob, vDistributionJobData $data)
	{
		$entryDistribution = EntryDistributionPeer::retrieveByPK($data->getEntryDistributionId());
		if(!$entryDistribution)
		{
			VidiunLog::err("Entry distribution [" . $data->getEntryDistributionId() . "] not found");
			return $dbBatchJob;
		}

		$distributionProfileId = $data->getDistributionProfileId();
		$distributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);

		// only feed spec v2 (rights feed) is setting the playlists on submit close action
		if ($distributionProfile &&
			$distributionProfile instanceof YouTubeDistributionProfile &&
			$distributionProfile->getFeedSpecVersion() == YouTubeDistributionFeedSpecVersion::VERSION_2)
		{
			self::saveCurrentPlaylistsToCustomData($data, $entryDistribution);
		}

		return $dbBatchJob;
	}

	/**
	 * @param vDistributionJobData $data
	 * @param $entryDistribution
	 */
	protected static function saveCurrentPlaylistsToCustomData(vDistributionJobData $data, $entryDistribution)
	{
		$providerData = $data->getProviderData();
		if ($providerData instanceof vYouTubeDistributionJobProviderData)
		{
			$entryDistribution->putInCustomData('currentPlaylists', $providerData->getCurrentPlaylists());
			$entryDistribution->save();
		}
	}
}
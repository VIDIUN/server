<?php
/**
 * @package plugins.ideticDistribution
 * @subpackage lib
 */
class vIdeticDistributionReportHandler implements vBatchJobStatusEventConsumer
{
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		if($dbBatchJob->getJobType() == ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_FETCH_REPORT))
			return true;
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{
		self::onDistributionFetchReportJobUpdated($dbBatchJob, $dbBatchJob->getData());
			
		return true;
	}

	/**
	 * @param BatchJob $dbBatchJob
	 * @param vDistributionFetchReportJobData $data
	 * @return BatchJob
	 */
	public static function onDistributionFetchReportJobUpdated(BatchJob $dbBatchJob, vDistributionFetchReportJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return self::onDistributionFetchReportJobFinished($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	/**
	 * @param BatchJob $dbBatchJob
	 * @param vDistributionFetchReportJobData $data
	 * @return BatchJob
	 */
	public static function onDistributionFetchReportJobFinished(BatchJob $dbBatchJob, vDistributionFetchReportJobData $data)
	{
		$entryDistribution = EntryDistributionPeer::retrieveByPK($data->getEntryDistributionId());
		if(!$entryDistribution)
		{
			VidiunLog::err("Entry distribution [" . $data->getEntryDistributionId() . "] not found");
			return $dbBatchJob;
		}
		
		$providerData = $data->getProviderData();
/*		if($providerData instanceof vIdeticDistributionJobProviderData)
		{
			$entryDistribution->putInCustomData('emailed', $providerData->getEmailed());
			$entryDistribution->putInCustomData('rated', $providerData->getRated());
			$entryDistribution->putInCustomData('blogged', $providerData->getBlogged());
			$entryDistribution->putInCustomData('reviewed', $providerData->getReviewed());
			$entryDistribution->putInCustomData('bookmarked', $providerData->getBookmarked());
			$entryDistribution->putInCustomData('playbackFailed', $providerData->getPlaybackFailed());
			$entryDistribution->putInCustomData('timeSpent', $providerData->getTimeSpent());
			$entryDistribution->putInCustomData('recommended', $providerData->getRecommended());
			
			$entryDistribution->save();
		}
	*/	
		return $dbBatchJob;
	}
}
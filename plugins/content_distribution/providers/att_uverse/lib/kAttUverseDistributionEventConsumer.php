<?php
/**
 * @package plugins.attUverseDistribution
 * @subpackage lib
 */
class vAttUverseDistributionEventConsumer implements vBatchJobStatusEventConsumer
{
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{		
		$jobTypes = array(
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE)
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
		$data = $dbBatchJob->getData();
		if (!$data instanceof vDistributionJobData)
		{	
			return true;
		}	
		
		$attUverseCoreValueType = vPluginableEnumsManager::apiToCore('DistributionProviderType', AttUverseDistributionPlugin::getApiValue(AttUverseDistributionProviderType::ATT_UVERSE));
		if ($data->getProviderType() != $attUverseCoreValueType)
		{			
			return true;
		}				
		
		$jobTypesToFinish = array(
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE)
		);
		if (in_array($dbBatchJob->getJobType(),$jobTypesToFinish) && $dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_FINISHED)
		{				
			return self::onDistributionJobFinished($dbBatchJob, $data);
		}
		
		if ($dbBatchJob->getJobType() == ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE) &&
			$dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_PENDING)
		{			
			vJobsManager::updateBatchJob($dbBatchJob, BatchJob::BATCHJOB_STATUS_FINISHED);
		}
		return true;
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
		
		$providerData = $data->getProviderData();
		if($providerData instanceof vAttUverseDistributionJobProviderData)
		{
			$entryDistribution->putInCustomData(AttUverseEntryDistributionCustomDataField::REMOTE_ASSET_FILE_URLS, $providerData->getRemoteAssetFileUrls());
			$entryDistribution->putInCustomData(AttUverseEntryDistributionCustomDataField::REMOTE_THUMBNAIL_FILE_URLS, $providerData->getRemoteThumbnailFileUrls());
			$entryDistribution->save();
		}
		
		return $dbBatchJob;
	}
	
}
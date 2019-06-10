<?php
/**
 * @package plugins.crossVidiunDistribution
 * @subpackage lib
 */
class vCrossVidiunDistributionEventsConsumer implements vBatchJobStatusEventConsumer
{
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{		
		$jobTypes = array(
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE),
		);
		
	    if(!in_array($dbBatchJob->getJobType(), $jobTypes))
	    {	
            // wrong job type
			return false;
		}
	    
	    $data = $dbBatchJob->getData();
		if (!$data instanceof vDistributionJobData)
		{	
		    VidiunLog::err('Wrong job data type');
			return false;
		}	
		
		$crossVidiunCoreValueType = vPluginableEnumsManager::apiToCore('DistributionProviderType', CrossVidiunDistributionPlugin::getApiValue(CrossVidiunDistributionProviderType::CROSS_VIDIUN));
		if ($data->getProviderType() == $crossVidiunCoreValueType)
		{		
			return true;
		}		
		
		// not the right provider
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob, BatchJob $twinJob = null)
	{		
		if ($dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_FINISHED)
		{				
			return self::onDistributionJobFinished($dbBatchJob);
		}
		
		return true;
	}

	/**
	 * @param BatchJob $dbBatchJob
	 * @return BatchJob
	 */
	public static function onDistributionJobFinished(BatchJob $dbBatchJob)
	{
	    $data = $dbBatchJob->getData();
	    
		$entryDistribution = EntryDistributionPeer::retrieveByPK($data->getEntryDistributionId());
		if(!$entryDistribution)
		{
			VidiunLog::err('Entry distribution ['.$data->getEntryDistributionId().'] not found');
			return $dbBatchJob;
		}
		
		$providerData = $data->getProviderData();
		if(!($providerData instanceof vCrossVidiunDistributionJobProviderData))
		{
		    VidiunLog::err('Wrong provider data class ['.get_class($providerData).']');
			return $dbBatchJob;
		}
		
		$entryDistribution->putInCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_FLAVOR_ASSETS, $providerData->getDistributedFlavorAssets());
		$entryDistribution->putInCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_THUMB_ASSETS, $providerData->getDistributedThumbAssets());
		$entryDistribution->putInCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_METADATA, $providerData->getDistributedMetadata());
		$entryDistribution->putInCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_CAPTION_ASSETS, $providerData->getDistributedCaptionAssets());
		$entryDistribution->putInCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_CUE_POINTS, $providerData->getDistributedCuePoints());
		$entryDistribution->putInCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_THUMB_CUE_POINTS, $providerData->getDistributedThumbCuePoints());
		$entryDistribution->putInCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_TIMED_THUMB_ASSETS, $providerData->getDistributedTimedThumbAssets());
		$entryDistribution->save();
		
		return $dbBatchJob;
	}
	
}
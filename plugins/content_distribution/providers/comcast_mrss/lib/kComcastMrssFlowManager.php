<?php
/**
 * @package plugins.comcastMrssDistribution
 * @subpackage lib
 */
class vComcastMrssFlowManager implements vBatchJobStatusEventConsumer
{
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		if($dbBatchJob->getJobType() == ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT))
			return true;
		
		if($dbBatchJob->getJobType() == ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE))
			return true;
		
		if($dbBatchJob->getJobType() == ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE))
			return true;
		
		if($dbBatchJob->getJobType() == ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_FETCH_REPORT))
			return true;
		
		if($dbBatchJob->getJobType() == ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_ENABLE))
			return true;
		
		if($dbBatchJob->getJobType() == ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DISABLE))
			return true;
		
		return false;
	}
	
	public function updatedJob(BatchJob $dbBatchJob)
	{
		$data = $dbBatchJob->getData();
		if (!$data instanceof vDistributionJobData)
			return true;
			
		$comcastMrssCoreValueType = vPluginableEnumsManager::apiToCore('DistributionProviderType', ComcastMrssDistributionPlugin::getApiValue(ComcastMrssDistributionProviderType::COMCAST_MRSS));
		if ($data->getProviderType() != $comcastMrssCoreValueType)
			return true;
			
		if ($dbBatchJob->getStatus() != BatchJob::BATCHJOB_STATUS_PENDING)
			return true;
			
		$jobTypesToFinish = array(
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_FETCH_REPORT),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_ENABLE),
			ContentDistributionPlugin::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DISABLE)
		);
		
		if (in_array($dbBatchJob->getJobType(), $jobTypesToFinish))
			vJobsManager::updateBatchJob($dbBatchJob, BatchJob::BATCHJOB_STATUS_FINISHED);
		
		return true;
	}
}
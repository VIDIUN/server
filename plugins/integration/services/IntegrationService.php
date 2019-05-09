<?php
/**
 * Integration service lets you dispatch integration tasks
 * @service integration
 * @package plugins.integration
 * @subpackage api.services
 */
class IntegrationService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		$partnerId = $this->getPartnerId();
		if (!EventNotificationPlugin::isAllowedPartner($partnerId))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, EventNotificationPlugin::PLUGIN_NAME);
			
		$this->applyPartnerFilterForClass('EventNotificationTemplate');
	}
		
	/**
	 * Dispatch integration task
	 * 
	 * @action dispatch
	 * @param VidiunIntegrationJobData $data
	 * @param VidiunBatchJobObjectType $objectType
	 * @param string $objectId
	 * @throws VidiunIntegrationErrors::INTEGRATION_DISPATCH_FAILED
	 * @return int
	 */		
	public function dispatchAction(VidiunIntegrationJobData $data, $objectType, $objectId)
	{
		$jobData = $data->toObject();
		$coreObjectType = vPluginableEnumsManager::apiToCore('BatchJobObjectType', $objectType);
		$job = vIntegrationFlowManager::addintegrationJob($coreObjectType, $objectId, $jobData);
		if(!$job)
			throw new VidiunAPIException(VidiunIntegrationErrors::INTEGRATION_DISPATCH_FAILED, $objectType);
			
		return $job->getId();
	}

	/**
	 * @action notify
	 * @disableTags TAG_WIDGET_SESSION,TAG_ENTITLEMENT_ENTRY,TAG_ENTITLEMENT_CATEGORY
	 * @param int $id integration job id
	 */
	public function notifyAction($id) 
	{
		$coreType = IntegrationPlugin::getBatchJobTypeCoreValue(IntegrationBatchJobType::INTEGRATION);
		$batchJob = BatchJobPeer::retrieveByPK($id);
		$invalidJobId = false;
		$invalidVs = false;
		
		if(!self::validateVs($batchJob))
		{
			$invalidVs = true;
			VidiunLog::err("vs not valid for notifying job [$id]");
		}
		elseif(!$batchJob)
		{
			$invalidJobId = true;
			VidiunLog::err("Job [$id] not found");
		}
		elseif($batchJob->getJobType() != $coreType)
		{
			$invalidJobId = true;
			VidiunLog::err("Job [$id] wrong type [" . $batchJob->getJobType() . "] expected [" . $coreType . "]");
		}
		elseif($batchJob->getStatus() != VidiunBatchJobStatus::ALMOST_DONE)
		{
			$invalidJobId = true;
			VidiunLog::err("Job [$id] wrong status [" . $batchJob->getStatus() . "] expected [" . VidiunBatchJobStatus::ALMOST_DONE . "]");
		}
		elseif($batchJob->getPartnerId() != vCurrentContext::getCurrentPartnerId())
		{
			$invalidVs = true;
			VidiunLog::err("Job [$id] of wrong partner [" . $batchJob->getPartnerId() . "] expected [" . vCurrentContext::getCurrentPartnerId() . "]");
		}

		if($invalidJobId)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_BATCHJOB_ID, $id);
		}
		if($invalidVs)
		{
			throw new VidiunAPIException(VidiunIntegrationErrors::INTEGRATION_NOTIFY_FAILED);
		}
			
		vJobsManager::updateBatchJob($batchJob, VidiunBatchJobStatus::FINISHED);
	}

	public static function validateVs($job)
	{	
		$dcParams = vDataCenterMgr::getCurrentDc();
		$token = $dcParams["secret"];
		
		$createdString = md5($job->getId() . $token);
		
		$vs = vCurrentContext::$vs_object;
		if($createdString == $vs->additional_data)
			return true;
		
		return false;
	}
}

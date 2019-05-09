<?php
/**
 * @package plugins.integration
 * @subpackage lib.events
 */
class vIntegrationFlowManager implements vBatchJobStatusEventConsumer
{
	const EXTERNAL_INTEGRATION_SERVICES_ROLE_NAME = "EXTERNAL_INTEGRATION_SERVICES_ROLE";
	const THREE_DAYS_IN_SECONDS = 259200;

	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{
		vEventsManager::raiseEvent(new vIntegrationJobClosedEvent($dbBatchJob));
		
		return true;
	}

	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		if($dbBatchJob->getJobType() != IntegrationPlugin::getBatchJobTypeCoreValue(IntegrationBatchJobType::INTEGRATION))
		{
			return false;
		} 
		 
		$closedStatusList = array(
			BatchJob::BATCHJOB_STATUS_FINISHED,
			BatchJob::BATCHJOB_STATUS_FAILED,
			BatchJob::BATCHJOB_STATUS_ABORTED,
			BatchJob::BATCHJOB_STATUS_FATAL,
			BatchJob::BATCHJOB_STATUS_FINISHED_PARTIALLY
		);
		
		return in_array($dbBatchJob->getStatus(), $closedStatusList);
	}
	
	public static function addintegrationJob($objectType, $objectId, vIntegrationJobData $data) 
	{
		$partnerId = vCurrentContext::getCurrentPartnerId();
		
		$providerType = $data->getProviderType();
		$integrationProvider = VidiunPluginManager::loadObject('IIntegrationProvider', $providerType);

		if(!$integrationProvider || !$integrationProvider->validatePermissions($partnerId))
		{
			VidiunLog::err("partner $partnerId not permitted with provider type $providerType");
			return false;
		}
		
		$batchJob = new BatchJob();
		$batchJob->setPartnerId($partnerId);
		$batchJob->setObjectType($objectType);
		$batchJob->setObjectId($objectId);
		
		if($objectType == BatchJobObjectType::ENTRY)
		{
			$batchJob->setEntryId($objectId);
		}
		elseif($objectType == BatchJobObjectType::ASSET)
		{
			$asset = assetPeer::retrieveById($objectId);
			if($asset)
				$batchJob->setEntryId($asset->getEntryId());
		}
		
		$batchJob->setStatus(BatchJob::BATCHJOB_STATUS_DONT_PROCESS);
		
		$jobType = IntegrationPlugin::getBatchJobTypeCoreValue(IntegrationBatchJobType::INTEGRATION);
		$batchJob = vJobsManager::addJob($batchJob, $data, $jobType, $providerType);
		
		if($integrationProvider->shouldSendCallBack())
		{
			$jobId = $batchJob->getId();
			$vs = self::generateVs($partnerId, $jobId);
			$dcParams = vDataCenterMgr::getCurrentDc();
			$dcUrl = $dcParams["url"];

			$callBackUrl = $dcUrl;
			$callBackUrl .= "/api_v3/index.php/service/integration_integration/action/notify";
			$callBackUrl .= "/id/$jobId/vs/$vs";

			$data = $batchJob->getData();
			$data->setCallbackNotificationUrl($callBackUrl);
			$batchJob->setData($data);
		}
		
		return vJobsManager::updateBatchJob($batchJob, BatchJob::BATCHJOB_STATUS_PENDING);
	}
	
	/**
	 * @return string
	 */
	public static function generateVs($partnerId, $tokenPrefix)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		$userSecret = $partner->getSecret();
		
		//actionslimit:1
		$privileges = vSessionBase::PRIVILEGE_SET_ROLE . ":" . self::EXTERNAL_INTEGRATION_SERVICES_ROLE_NAME;
		$privileges .= "," . vSessionBase::PRIVILEGE_ACTIONS_LIMIT . ":1";
		
		$dcParams = vDataCenterMgr::getCurrentDc();
		$token = $dcParams["secret"];
		$additionalData = md5($tokenPrefix . $token);
		
		$vs = "";
		$creationSucces = vSessionUtils::startVSession ($partnerId, $userSecret, "", $vs, self::THREE_DAYS_IN_SECONDS, VidiunSessionType::USER, "", $privileges, null,$additionalData);
		if ($creationSucces >= 0 )
				return $vs;
		
		return false;
	}
}

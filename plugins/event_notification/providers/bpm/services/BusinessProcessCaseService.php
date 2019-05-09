<?php
/**
 * Business-process case service lets you get information about processes
 * @service businessProcessCase
 * @package plugins.businessProcessNotification
 * @subpackage api.services
 */
class BusinessProcessCaseService extends VidiunBaseService
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
	 * Abort business-process case
	 * 
	 * @action abort
	 * @param VidiunEventNotificationEventObjectType $objectType
	 * @param string $objectId
	 * @param int $businessProcessStartNotificationTemplateId
	 *
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND
	 * @throws VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_CASE_NOT_FOUND
	 * @throws VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_SERVER_NOT_FOUND
	 */		
	public function abortAction($objectType, $objectId, $businessProcessStartNotificationTemplateId)
	{
		$dbObject = vEventNotificationFlowManager::getObject($objectType, $objectId);
		if(!$dbObject)
		{
			throw new VidiunAPIException(VidiunErrors::OBJECT_NOT_FOUND);
		}
		
		$dbTemplate = EventNotificationTemplatePeer::retrieveByPK($businessProcessStartNotificationTemplateId);
		if(!$dbTemplate || !($dbTemplate instanceof BusinessProcessStartNotificationTemplate))
		{
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND, $businessProcessStartNotificationTemplateId);
		}
		
		$caseIds = $dbTemplate->getCaseIds($dbObject, false);
		if(!count($caseIds))
		{
			throw new VidiunAPIException(VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_CASE_NOT_FOUND);
		}
		
		$dbBusinessProcessServer = BusinessProcessServerPeer::retrieveByPK($dbTemplate->getServerId());
		if (!$dbBusinessProcessServer)
		{
			throw new VidiunAPIException(VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_SERVER_NOT_FOUND, $dbTemplate->getServerId());
		}
		
		$server = new VidiunActivitiBusinessProcessServer();
		$server->fromObject($dbBusinessProcessServer);
		$provider = vBusinessProcessProvider::get($server);
		
		foreach($caseIds as $caseId)
		{
			$provider->abortCase($caseId);
		}
	}

	/**
	 * Server business-process case diagram
	 * 
	 * @action serveDiagram
	 * @param VidiunEventNotificationEventObjectType $objectType
	 * @param string $objectId
	 * @param int $businessProcessStartNotificationTemplateId
	 * @return file
	 *
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND
	 * @throws VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_CASE_NOT_FOUND
	 * @throws VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_SERVER_NOT_FOUND
	 */		
	public function serveDiagramAction($objectType, $objectId, $businessProcessStartNotificationTemplateId)
	{
		$dbObject = vEventNotificationFlowManager::getObject($objectType, $objectId);
		if(!$dbObject)
		{
			throw new VidiunAPIException(VidiunErrors::OBJECT_NOT_FOUND);
		}
		
		$dbTemplate = EventNotificationTemplatePeer::retrieveByPK($businessProcessStartNotificationTemplateId);
		if(!$dbTemplate || !($dbTemplate instanceof BusinessProcessStartNotificationTemplate))
		{
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND, $businessProcessStartNotificationTemplateId);
		}
		
		$caseIds = $dbTemplate->getCaseIds($dbObject, false);
		if(!count($caseIds))
		{
			throw new VidiunAPIException(VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_CASE_NOT_FOUND);
		}
		
		$dbBusinessProcessServer = BusinessProcessServerPeer::retrieveByPK($dbTemplate->getServerId());
		if (!$dbBusinessProcessServer)
		{
			throw new VidiunAPIException(VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_SERVER_NOT_FOUND, $dbTemplate->getServerId());
		}
		
		$businessProcessServer = VidiunBusinessProcessServer::getInstanceByType($dbBusinessProcessServer->getType());
		$businessProcessServer->fromObject($dbBusinessProcessServer);
		$provider = vBusinessProcessProvider::get($businessProcessServer);
		
		$caseId = end($caseIds);
		
		$filename = myContentStorage::getFSCacheRootPath() . 'bpm_diagram/bpm_';
		$filename .= $objectId . '_';
		$filename .= $businessProcessStartNotificationTemplateId . '_';
		$filename .= $caseId . '.jpg';
		
		$provider->getCaseDiagram($caseId, $filename);
		$mimeType = vFile::mimeType($filename);			
		return $this->dumpFile($filename, $mimeType);
	}
	
	/**
	 * list business-process cases
	 * 
	 * @action list
	 * @param VidiunEventNotificationEventObjectType $objectType
	 * @param string $objectId
	 * @return VidiunBusinessProcessCaseArray
	 * 
	 * @throws VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_CASE_NOT_FOUND
	 * @throws VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_SERVER_NOT_FOUND
	 */
	public function listAction($objectType, $objectId)
	{
		$dbObject = vEventNotificationFlowManager::getObject($objectType, $objectId);
		if(!$dbObject)
		{
			throw new VidiunAPIException(VidiunErrors::OBJECT_NOT_FOUND);
		}
		
		$cases = BusinessProcessCasePeer::retrieveCasesByObjectIdObjecType($objectId, $objectType);
		if(!count($cases))
		{
			throw new VidiunAPIException(VidiunBusinessProcessNotificationErrors::BUSINESS_PROCESS_CASE_NOT_FOUND);
		}
		
		$array = new VidiunBusinessProcessCaseArray();
		foreach($cases as $case)
		{
			/* @var $case BusinessProcessCase */
			$dbBusinessProcessServer = BusinessProcessServerPeer::retrieveByPK($case->getServerId());
			if (!$dbBusinessProcessServer)
			{
				VidiunLog::info("Business-Process server [" . $case->getServerId() . "] not found");
				continue;
			}
			
			$businessProcessServer = VidiunBusinessProcessServer::getInstanceByType($dbBusinessProcessServer->getType());
			$businessProcessServer->fromObject($dbBusinessProcessServer);
			$provider = vBusinessProcessProvider::get($businessProcessServer);
			if(!$provider)
			{
				VidiunLog::info("Provider [" . $businessProcessServer->type . "] not found");
				continue;
			}

			$latestCaseId = $case->getCaseId();
			if($latestCaseId)
			{
				try {
					$case = $provider->getCase($latestCaseId);
					$businessProcessCase = new VidiunBusinessProcessCase();
					$businessProcessCase->businessProcessStartNotificationTemplateId = $templateId;
					$businessProcessCase->fromObject($case);
					$array[] = $businessProcessCase;
				} catch (ActivitiClientException $e) {
					VidiunLog::err("Case [$latestCaseId] not found: " . $e->getMessage());
				}
			}
		}
		
		return $array;
	}
}

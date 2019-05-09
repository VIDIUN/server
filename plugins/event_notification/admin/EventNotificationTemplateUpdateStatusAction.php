<?php
/**
 * @package plugins.eventNotification
 * @subpackage admin
 */
class EventNotificationTemplateUpdateStatusAction extends VidiunApplicationPlugin
{
	public function __construct()
	{
		$this->action = 'updateStatusEventNotificationTemplates';
	}
	
	/* (non-PHPdoc)
	 * @see VidiunApplicationPlugin::getTemplatePath()
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}
	
	/* (non-PHPdoc)
	 * @see VidiunApplicationPlugin::getRequiredPermissions()
	 */
	public function getRequiredPermissions()
	{
		return array(Vidiun_Client_Enum_PermissionName::SYSTEM_ADMIN_EVENT_NOTIFICATION_MODIFY);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunApplicationPlugin::doAction()
	 */
	public function doAction(Zend_Controller_Action $action)
	{
		$action->getHelper('viewRenderer')->setNoRender();
		$templateId = $this->_getParam('template_id');
		$status = $this->_getParam('status');
		$client = Infra_ClientHelper::getClient();
		$eventNotificationPlugin = Vidiun_Client_EventNotification_Plugin::get($client);
		
		$partnerId = $this->_getParam('partner_id');
		if($partnerId)
			Infra_ClientHelper::impersonate($partnerId);
		
		try
		{
			$eventNotificationPlugin->eventNotificationTemplate->updateStatus($templateId, $status);
			echo $action->getHelper('json')->sendJson('ok', false);
		}
		catch(Exception $e)
		{
			VidiunLog::err($e->getMessage() . "\n" . $e->getTraceAsString());
			echo $action->getHelper('json')->sendJson($e->getMessage(), false);
		}
	}
}


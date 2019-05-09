<?php
/**
 * @package plugins.reach
 * @subpackage Admin
 */
class ReachProfileSetStatusAction extends VidiunApplicationPlugin
{

	/**
	 * @return string - absolute file path of the phtml template
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}

	public function doAction(Zend_Controller_Action $action)
	{
		$action->getHelper('layout')->disableLayout();
		$reachProfileId = $this->_getParam('reachProfileId');
		$newStatus = $this->_getParam('reachProfileStatus');
		$partnerId = $this->_getParam('partnerId');

		$client = Infra_ClientHelper::getClient();
		$reachPluginClient = Vidiun_Client_Reach_Plugin::get($client);
		Infra_ClientHelper::impersonate($partnerId);
		try
		{
			if  ( $newStatus == Vidiun_Client_Reach_Enum_ReachProfileStatus::DELETED )
				$res = $reachPluginClient->reachProfile->delete($reachProfileId);
			else
				$res = $reachPluginClient->reachProfile->updateStatus($reachProfileId, $newStatus);
			echo $action->getHelper('json')->sendJson('ok', false);
		} catch (Exception $e)
		{
			VidiunLog::err($e->getMessage() . "\n" . $e->getTraceAsString());
			echo $action->getHelper('json')->sendJson($e->getMessage(), false);
		}
		Infra_ClientHelper::unimpersonate();
	}
}
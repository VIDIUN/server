<?php
/**
 * @package plugins.reach
 * @subpackage Admin
 */
class CatalogItemSetStatusAction extends VidiunApplicationPlugin
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
		$catalogItemId = $this->_getParam('catalogItemId');
		$newStatus = $this->_getParam('catalogItemStatus');
		$partnerId = $this->_getParam('partnerId');

		$client = Infra_ClientHelper::getClient();
		$reachPluginClient = Vidiun_Client_Reach_Plugin::get($client);
		Infra_ClientHelper::impersonate($partnerId);
		try
		{
			if  ( $newStatus == Vidiun_Client_Reach_Enum_VendorCatalogItemStatus::DELETED )
				$res = $reachPluginClient->vendorCatalogItem->delete($catalogItemId);
			else
				$res = $reachPluginClient->vendorCatalogItem->updateStatus($catalogItemId, $newStatus);
			echo $action->getHelper('json')->sendJson('ok', false);
		} catch (Exception $e)
		{
			VidiunLog::err($e->getMessage() . "\n" . $e->getTraceAsString());
			echo $action->getHelper('json')->sendJson($e->getMessage(), false);
		}
		Infra_ClientHelper::unimpersonate();
	}
}
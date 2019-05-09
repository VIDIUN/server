<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage admin
 */
class GenericDistributionProviderDeleteAction extends VidiunApplicationPlugin
{
	public function __construct()
	{
		$this->action = 'deleteGenericDistributionProvider';
	}
	
	/**
	 * @return string - absolute file path of the phtml template
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}
	
	public function getRequiredPermissions()
	{
		return array(Vidiun_Client_Enum_PermissionName::SYSTEM_ADMIN_CONTENT_DISTRIBUTION_MODIFY);
	}
	
	public function doAction(Zend_Controller_Action $action)
	{
		$action->getHelper('viewRenderer')->setNoRender();
		$providerId = $this->_getParam('provider_id');
		$client = Infra_ClientHelper::getClient();
		$contentDistributionPlugin = Vidiun_Client_ContentDistribution_Plugin::get($client);
		try
		{
			$contentDistributionPlugin->genericDistributionProvider->delete($providerId);
			echo $action->getHelper('json')->sendJson('ok', false);
		}
		catch(Exception $e)
		{
			VidiunLog::err($e->getMessage() . "\n" . $e->getTraceAsString());
			echo $action->getHelper('json')->sendJson($e->getMessage(), false);
		}
	}
}


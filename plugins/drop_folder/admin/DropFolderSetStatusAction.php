<?php
/**
 * @package plugins.dropFolder
 * @subpackage Admin
 */
class DropFolderSetStatusAction extends VidiunApplicationPlugin
{
	
	/**
	 * @return string - absolute file path of the phtml template
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}
	
	public function getRequiredPermissions()
	{
		return array(Vidiun_Client_Enum_PermissionName::SYSTEM_ADMIN_DROP_FOLDER_MODIFY);
	}
	
	public function doAction(Zend_Controller_Action $action)
	{
		$action->getHelper('layout')->disableLayout();
		$dropFolderId = $this->_getParam('dropFolderId');
		$newStatus = $this->_getParam('dropFolderStatus');
		
		$client = Infra_ClientHelper::getClient();
		$dropFolderPluginClient = Vidiun_Client_DropFolder_Plugin::get($client);
		
		$updatedDropFolder = new Vidiun_Client_DropFolder_Type_DropFolder();
		$updatedDropFolder->status = $newStatus;
		
		try
		{
			$updatedDropFolder = $dropFolderPluginClient->dropFolder->update($dropFolderId, $updatedDropFolder);
			echo $action->getHelper('json')->sendJson('ok', false);
		}
		catch(Exception $e)
		{
			VidiunLog::err($e->getMessage() . "\n" . $e->getTraceAsString());
			echo $action->getHelper('json')->sendJson($e->getMessage(), false);
		}
	}
}


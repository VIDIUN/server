<?php
/**
 * @package Core
 * @subpackage externalWidgets
 */
class servefileAction extends sfAction
{
	/**
	 * Will forward to the regular swf player according to the widget_id 
	 */
	public function execute()
	{
		requestUtils::handleConditionalGet();
		
		$file_sync_id = $this->getRequestParameter( "id" );
		$hash = $this->getRequestParameter( "hash" );
		$file_name = $this->getRequestParameter( "fileName" );
		if ($file_name) {
			$file_name = base64_decode($file_name);
		}
	
		$file_sync = FileSyncPeer::retrieveByPk ( $file_sync_id );
		if ( ! $file_sync )
		{
			$current_dc_id = vDataCenterMgr::getCurrentDcId();
			$error = "DC[$current_dc_id]: Cannot find FileSync with id [$file_sync_id]";
			VidiunLog::err($error);
			VExternalErrors::dieError(VExternalErrors::FILE_NOT_FOUND);
		}
		
		VidiunMonitorClient::initApiMonitor(false, 'extwidget.serveFile', $file_sync->getPartnerId());
		
		vDataCenterMgr::serveFileToRemoteDataCenter ( $file_sync , $hash, $file_name ); 
		die();
	}
}

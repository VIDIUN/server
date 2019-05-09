<?php
/**
 * @package Core
 * @subpackage externalWidgets
 */
class serveMultiFileAction extends sfAction
{
	/**
	 * Serves multiple files for synchronization between datacenters 
	 */
	public function execute()
	{
		$fileSyncIds = $this->getRequestParameter( "ids" );
		$hash = $this->getRequestParameter( "hash" );

		// validate hash
		$currentDc = vDataCenterMgr::getCurrentDc();
		$currentDcId = $currentDc["id"];
		$expectedHash = md5($currentDc["secret" ] . $fileSyncIds);
		if ($hash !== $expectedHash)  
		{
			$error = "Invalid hash - ids [$fileSyncIds] got [$hash] expected [$expectedHash]";
			VidiunLog::err($error); 
			VExternalErrors::dieError(VExternalErrors::INVALID_TOKEN);
		}
		
		// load file syncs
		$fileSyncs = FileSyncPeer::retrieveByPks(explode(',', $fileSyncIds));
		if ($fileSyncs)
		{
			VidiunMonitorClient::initApiMonitor(false, 'extwidget.serveMultiFile', $fileSyncs[0]->getPartnerId());
		}
		
		// resolve file syncs
		$resolvedFileSyncs = array();
		foreach ($fileSyncs as $fileSync)
		{
			if ( $fileSync->getDc() != $currentDcId )
			{
				$error = "FileSync id [".$fileSync->getId()."] does not belong to this DC";
				VidiunLog::err($error);
				VExternalErrors::dieError(VExternalErrors::BAD_QUERY);
			}
			
			// resolve if file_sync is link
			$fileSyncResolved = vFileSyncUtils::resolve($fileSync);
			
			// check if file sync path leads to a file or a directory
			$resolvedPath = $fileSyncResolved->getFullPath();
			if (is_dir($resolvedPath))
			{
				$error = "FileSync id [".$fileSync->getId()."] is a directory";
				VidiunLog::err($error);
				VExternalErrors::dieError(VExternalErrors::BAD_QUERY);
			}
						
			if (!file_exists($resolvedPath))
			{
				$error = "Path [$resolvedPath] for fileSync id [".$fileSync->getId()."] does not exist";
				VidiunLog::err($error);
				continue;
			}
			
			$resolvedFileSyncs[$fileSync->getId()] = $fileSyncResolved;
		}
		
		$boundary = md5(uniqid('', true));
		header('Content-Type: multipart/form-data; boundary='.$boundary);

		foreach ($resolvedFileSyncs as $id => $resolvedFileSync)
		{
			echo "--$boundary\n";
			echo "Content-Type: application/octet-stream\n";
			echo "Content-Disposition: form-data; name=\"$id\"\n\n";

			echo vFileSyncUtils::getLocalContentsByFileSync($resolvedFileSync);//already checked that file
			echo "\n";
		}
		echo "--$boundary--\n";
		
		VExternalErrors::dieGracefully();
	}
}

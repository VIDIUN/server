<?php
/**
 * @package Core
 * @subpackage externalWidgets
 */
class downloadAction extends sfAction
{
	
	/**
	 * Will forward to the regular swf player according to the widget_id 
	 */
	public function execute()
	{
		$entryId = $this->getRequestParameter("entry_id");
		$flavorId = $this->getRequestParameter("flavor");
		$fileName = $this->getRequestParameter("file_name");
		$fileName = basename($fileName);
		$vsStr = $this->getRequestParameter("vs");
		$referrer = $this->getRequestParameter("referrer");
		$referrer = base64_decode($referrer);
		if (!is_string($referrer)) // base64_decode can return binary data
			$referrer = "";
			
		$entry = null;
		
		if($vsStr)
		{
			try {
				vCurrentContext::initVsPartnerUser($vsStr);
			}
			catch (Exception $ex)
			{
				VExternalErrors::dieError(VExternalErrors::INVALID_VS);	
			}
		}
		else
		{
			$entry = vCurrentContext::initPartnerByEntryId($entryId);
			if(!$entry)
				VExternalErrors::dieError(VExternalErrors::ENTRY_NOT_FOUND);
		}
		
		vEntitlementUtils::initEntitlementEnforcement();
		
		if (!$entry)
		{
			$entry = entryPeer::retrieveByPK($entryId);
			
			if(!$entry)
				VExternalErrors::dieError(VExternalErrors::ENTRY_NOT_FOUND);
		}
		else
		{
			if(!vEntitlementUtils::isEntryEntitled($entry))
				VExternalErrors::dieError(VExternalErrors::ENTRY_NOT_FOUND);
		}
		
		VidiunMonitorClient::initApiMonitor(false, 'extwidget.download', $entry->getPartnerId());
		
		myPartnerUtils::blockInactivePartner($entry->getPartnerId());
		
		$shouldPreview = false;
		$securyEntryHelper = new VSecureEntryHelper($entry, $vsStr, $referrer, ContextType::DOWNLOAD);
		if ($securyEntryHelper->shouldPreview()) { 
			$shouldPreview = true;
		} else { 
			$securyEntryHelper->validateForDownload();
		}
		
		$flavorAsset = null;

		if ($flavorId) 
		{
			// get flavor asset
			$flavorAsset = assetPeer::retrieveById($flavorId);
			if (is_null($flavorAsset) || !$flavorAsset->isLocalReadyStatus())
				VExternalErrors::dieError(VExternalErrors::FLAVOR_NOT_FOUND);
			
			// the request flavor should belong to the requested entry
			if ($flavorAsset->getEntryId() != $entryId)
				VExternalErrors::dieError(VExternalErrors::FLAVOR_NOT_FOUND);
				
			if(!$securyEntryHelper->isAssetAllowed($flavorAsset))
				VExternalErrors::dieError(VExternalErrors::FLAVOR_NOT_FOUND);
		}
		else // try to find some flavor
		{
			$flavorAssets = assetPeer::retrieveReadyWebByEntryId($entry->getId());
			foreach ($flavorAssets as $curFlavorAsset) 
			{
				if($securyEntryHelper->isAssetAllowed($curFlavorAsset))
				{	
					$flavorAsset = $curFlavorAsset;
					break;
				}
			}
		}
		
		// Gonen 26-04-2010: in case entry has no flavor with 'mbr' tag - we return the source
		if(!$flavorAsset && ($entry->getMediaType() == entry::ENTRY_MEDIA_TYPE_VIDEO || $entry->getMediaType() == entry::ENTRY_MEDIA_TYPE_AUDIO))
		{
			$flavorAsset = assetPeer::retrieveOriginalByEntryId($entryId);
			if(!$securyEntryHelper->isAssetAllowed($flavorAsset))
			{
				$flavorAsset = null;
			}
		}
		
		if ($flavorAsset)
		{
			$syncKey = $this->getSyncKeyAndForFlavorAsset($entry, $flavorAsset);
		}
		else
		{
			$syncKey = $this->getBestSyncKeyForEntry($entry);
		}
		
		if (is_null($syncKey))
			VExternalErrors::dieError(VExternalErrors::FILE_NOT_FOUND);
			
		$this->handleFileSyncRedirection($syncKey);

		list ($fileSync,$local) = vFileSyncUtils::getReadyFileSyncForKey($syncKey, false, false);
		if (!$fileSync)
			VExternalErrors::dieError(VExternalErrors::FILE_NOT_FOUND);
		/**@var $fileSync FileSync */
		$filePath = $fileSync->getFullPath();

		list($fileBaseName, $fileExt) = vAssetUtils::getFileName($entry, $flavorAsset);

		if (!$fileName)
			$fileName = $fileBaseName;
		
		if ($fileExt && !is_dir($filePath))
			$fileName = $fileName . '.' . $fileExt;
		
		$preview = 0;
		if($shouldPreview && $flavorAsset) {
			$preview = $flavorAsset->estimateFileSize($entry, $securyEntryHelper->getPreviewLength());
		} else if(vCurrentContext::$vs_object) {
			$preview = vCurrentContext::$vs_object->getPrivilegeValue(vSessionBase::PRIVILEGE_PREVIEW, 0);
		}
		
               //enable downloading file_name which inside the flavor asset directory
                if(is_dir($filePath))
                {
                        $filePath = $filePath . DIRECTORY_SEPARATOR . $fileName;
                        $fileSize = null;
                }
                else
                {
                        $fileSize = $fileSync->getFileSize();
                }
                $this->dumpFile($filePath, $fileName, $preview, $fileSync->getEncryptionKey(), $fileSync->getIv(), $fileSize);
	
		VExternalErrors::dieGracefully(); // no view
	}
	
	private function getSyncKeyAndForFlavorAsset(entry $entry, flavorAsset $flavorAsset)
	{
		$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		return $syncKey;
	}
	
	private function getBestSyncKeyForEntry(entry $entry)
	{
		$entryType = $entry->getType();
		$entryMediaType = $entry->getMediaType();
		$syncKey = null;
		switch($entryType)
		{
			case entryType::MEDIA_CLIP: 
				switch ($entryMediaType)
				{
					case entry::ENTRY_MEDIA_TYPE_IMAGE:
						$syncKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
						break;
				}
				break;
		}
		
		return $syncKey;
	}

	private function dumpFile($file_path, $file_name, $limit_file_size = 0, $key = null, $iv = null, $fileSize = null)
	{
		$file_name = str_replace("\n", ' ', $file_name);
		$relocate = $this->getRequestParameter("relocate");
		$directServe = $this->getRequestParameter("direct_serve");

		if (!$relocate)
		{
			$url = $_SERVER["REQUEST_URI"];
			if (strpos($url, "?") !== false) // when query string exists, just remove it (otherwise it might cause redirect loops)
			{
				$url .= "&relocate=";
			}
			else
			{
				$url .= "/relocate/";
			}
				
			$url .= vString::stripInvalidUrlChars($file_name);

			vFile::cacheRedirect($url);

			header("Location: {$url}");
			VExternalErrors::dieGracefully();
		}
		else
		{
			if(!$directServe)
				header("Content-Disposition: attachment; filename=\"$file_name\"");
				
			$mime_type = vFile::mimeType($file_path);
			vFileUtils::dumpFile($file_path, $mime_type, null, $limit_file_size, $key, $iv, $fileSize);
		}
	}
	
	private function handleFileSyncRedirection(FileSyncKey $syncKey)
	{
		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
		
		if (is_null($fileSync))
			VExternalErrors::dieError(VExternalErrors::FILE_NOT_FOUND);
			
		if (!$local)
		{
			$url = vDataCenterMgr::getRedirectExternalUrl($fileSync);
			VExternalErrors::terminateDispatch();
			$this->redirect($url);
		}
	}
}

<?php
/**
 * @package Core
 * @subpackage externalWidgets
 */
class serveIsmAction extends sfAction
{
	/**
	 * @var entry
	 */
	private $entry = null;

	/**
	 * @var asset
	 */
	private $flavorAsset = null;
	
	/**
	 * Will forward to the regular swf player according to the widget_id 
	 */
	public function execute()
	{
		// where file is {entryId/flavorId}.{ism,ismc,ismv}
		
		$objectId = $type = null;
		$objectIdStr = $this->getRequestParameter( "objectId" );
		if($objectIdStr)
			list($objectId, $type) = @explode(".", $objectIdStr);
		
		if (!$type || !$objectId)
			VExternalErrors::dieError(VExternalErrors::MISSING_PARAMETER);
			
		$vs = $this->getRequestParameter( "vs" );
		$referrer = base64_decode($this->getRequestParameter("referrer"));
		if (!is_string($referrer)) // base64_decode can return binary data
			$referrer = '';
						
		$syncKey = $this->getFileSyncKey($objectId, $type);
				
		VidiunMonitorClient::initApiMonitor(false, 'extwidget.serveIsm', $this->entry->getPartnerId());
		
		myPartnerUtils::enforceDelivery($this->entry, $this->flavorAsset);
		
		if (!vFileSyncUtils::file_exists($syncKey, false))
		{
			list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
			
			if (is_null($fileSync))
			{
				VidiunLog::log("Error - no FileSync for type [$type] objectId [$objectId]");
				VExternalErrors::dieError(VExternalErrors::FILE_NOT_FOUND);
			}
			
			$remoteUrl = vDataCenterMgr::getRedirectExternalUrl($fileSync);
			vFileUtils::dumpUrl($remoteUrl);
		}
		
		if($type == 'ism')
		{
			$fileData = vExtWidgetUtils::fixIsmManifestForReplacedEntry($syncKey, $this->entry, true);
			$renderer = new vRendererString($fileData, 'image/ism');
			$renderer->output();
            VExternalErrors::dieGracefully();	
		}
		else 
		{
			vFileSyncUtils::dumpFileByFileSyncKey($syncKey);
		}
		
		
	}
	
	private function getFileSyncKey($objectId, $type)
	{
		$key = null;
		$hasVersion = strlen($objectId) != 10;
		$version = null;
		$object = null;
		$subType = null;
		$isAsset = false;
		$entryId = null;
		
		if($hasVersion)
		{
			list($objectId, $version, $subType, $isAsset, $entryId) = vExtWidgetUtils::parseObjectId($objectId);
		}

		switch ($type)
		{
			case 'ism':
				//To Remove - Until the migration process from asset sub type 3 to asset sub type 1 will be completed we need to support both formats
				if($subType == flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET || $subType == flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ISM)
					$isAsset = true;
				else 
					$subType = entry::FILE_SYNC_ENTRY_SUB_TYPE_ISM;
				break;
			case 'ismc':
				if($subType == flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ISMC)
					$isAsset = true;
				if($isAsset)
					$subType = flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ISMC;
				else
					$subType = entry::FILE_SYNC_ENTRY_SUB_TYPE_ISMC;
				break;
			case 'ismv':
			case 'isma':
				$subType = flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET;
				$isAsset = true;
				break;
			default:
				VExternalErrors::dieError(VExternalErrors::INVALID_ISM_FILE_TYPE);
		}
		
		$object = $this->getObject($objectId, $isAsset);
		if(!$object)
			VExternalErrors::dieError(VExternalErrors::FLAVOR_NOT_FOUND);
			
		
		$key = $object->getSyncKey($subType, $version);
		return $key;
	}
	

	
	private function getObject($objectId, $isAsset)
	{
		if($isAsset)
		{
			$this->flavorAsset = assetPeer::retrieveById($objectId);
			if (is_null($this->flavorAsset))
				VExternalErrors::dieError(VExternalErrors::FLAVOR_NOT_FOUND);
				
			$this->entry = entryPeer::retrieveByPK($this->flavorAsset->getEntryId());
			if (is_null($this->entry))
				VExternalErrors::dieError(VExternalErrors::ENTRY_NOT_FOUND);
				
			return $this->flavorAsset;
		}	
		else
		{
			$this->entry = entryPeer::retrieveByPK($objectId);
			if (is_null($this->entry))
				VExternalErrors::dieError(VExternalErrors::ENTRY_NOT_FOUND);
				
			return $this->entry;
		}				
	}
	

	

}

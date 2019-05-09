<?php

/**
 * Manage file assets
 *
 * @service fileAsset
 */
class FileAssetService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('FileAsset');
		$this->applyPartnerFilterForClass('uiConf');
	}
	
	/**
	 * Add new file asset
	 * 
	 * @action add
	 * @param VidiunFileAsset $fileAsset
	 * @return VidiunFileAsset
	 */
	function addAction(VidiunFileAsset $fileAsset)
	{
		$dbFileAsset = $fileAsset->toInsertableObject();
		$dbFileAsset->setPartnerId($this->getPartnerId());
		$dbFileAsset->setStatus(VidiunFileAssetStatus::PENDING);
		$dbFileAsset->save();
		
		$fileAsset = new VidiunFileAsset();
		$fileAsset->fromObject($dbFileAsset, $this->getResponseProfile());
		return $fileAsset;
	}
	
	/**
	 * Get file asset by id
	 * 
	 * @action get
	 * @param bigint $id
	 * @return VidiunFileAsset
	 * @vsIgnored
	 * 
	 * @throws VidiunErrors::FILE_ASSET_ID_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbFileAsset = FileAssetPeer::retrieveByPK($id);
		if (!$dbFileAsset)
			throw new VidiunAPIException(VidiunErrors::FILE_ASSET_ID_NOT_FOUND, $id);
			
		$fileAsset = new VidiunFileAsset();
		$fileAsset->fromObject($dbFileAsset, $this->getResponseProfile());
		return $fileAsset;
	}
	
	/**
	 * Update file asset by id
	 * 
	 * @action update
	 * @param bigint $id
	 * @param VidiunFileAsset $fileAsset
	 * @return VidiunFileAsset
	 * 
	 * @throws VidiunErrors::FILE_ASSET_ID_NOT_FOUND
	 */
	function updateAction($id, VidiunFileAsset $fileAsset)
	{
		$dbFileAsset = FileAssetPeer::retrieveByPK($id);
		if (!$dbFileAsset)
			throw new VidiunAPIException(VidiunErrors::FILE_ASSET_ID_NOT_FOUND, $id);
		
		$fileAsset->toUpdatableObject($dbFileAsset);
		$dbFileAsset->save();
		
		$fileAsset = new VidiunFileAsset();
		$fileAsset->fromObject($dbFileAsset, $this->getResponseProfile());
		return $fileAsset;
	}
	
	/**
	 * Delete file asset by id
	 * 
	 * @action delete
	 * @param bigint $id
	 * 
	 * @throws VidiunErrors::FILE_ASSET_ID_NOT_FOUND
	 */
	function deleteAction($id)
	{
		$dbFileAsset = FileAssetPeer::retrieveByPK($id);
		if (!$dbFileAsset)
			throw new VidiunAPIException(VidiunErrors::FILE_ASSET_ID_NOT_FOUND, $id);

		$dbFileAsset->setStatus(VidiunFileAssetStatus::DELETED);
		$dbFileAsset->save();
	}

	/**
	 * Serve file asset by id
	 *  
	 * @action serve
	 * @param bigint $id
	 * @return file
	 * @vsIgnored
	 *  
	 * @throws VidiunErrors::FILE_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::FILE_DOESNT_EXIST
	 */
	public function serveAction($id)
	{
		$dbFileAsset = FileAssetPeer::retrieveByPK($id);
		if (!$dbFileAsset)
			throw new VidiunAPIException(VidiunErrors::FILE_ASSET_ID_NOT_FOUND, $id);
		
		return $this->serveFile($dbFileAsset, FileAsset::FILE_SYNC_ASSET, $dbFileAsset->getName());
	}
	
    /**
     * Set content of file asset
     *
     * @action setContent
     * @param bigint $id
     * @param VidiunContentResource $contentResource
     * @return VidiunFileAsset
	 * @throws VidiunErrors::FILE_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED 
     */
    function setContentAction($id, VidiunContentResource $contentResource)
    {
		$dbFileAsset = FileAssetPeer::retrieveByPK($id);
		if (!$dbFileAsset)
			throw new VidiunAPIException(VidiunErrors::FILE_ASSET_ID_NOT_FOUND, $id);
		
		$vContentResource = $contentResource->toObject();
    	$this->attachContentResource($dbFileAsset, $vContentResource);
		
		$fileAsset = new VidiunFileAsset();
		$fileAsset->fromObject($dbFileAsset, $this->getResponseProfile());
		return $fileAsset;
    }
    
	/**
	 * @param FileAsset $dbFileAsset
	 * @param vContentResource $contentResource
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 */
	protected function attachContentResource(FileAsset $dbFileAsset, vContentResource $contentResource)
	{
    	switch($contentResource->getType())
    	{
			case 'vLocalFileResource':
				return $this->attachLocalFileResource($dbFileAsset, $contentResource);
				
			case 'vFileSyncResource':
				return $this->attachFileSyncResource($dbFileAsset, $contentResource);
				
			default:
				$msg = "Resource of type [" . get_class($contentResource) . "] is not supported";
				VidiunLog::err($msg);
				
				throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($contentResource));
    	}
    }
    
	/**
	 * @param FileAsset $dbFileAsset
	 * @param vLocalFileResource $contentResource
	 */
	protected function attachLocalFileResource(FileAsset $dbFileAsset, vLocalFileResource $contentResource)
	{
		if($contentResource->getIsReady())
			return $this->attachFile($dbFileAsset, $contentResource->getLocalFilePath(), $contentResource->getKeepOriginalFile());
			
		$dbFileAsset->setStatus(FileAssetStatus::UPLOADING);
		$dbFileAsset->save();
		
		$contentResource->attachCreatedObject($dbFileAsset);
    }
    
	/**
	 * @param FileAsset $dbFileAsset
	 * @param vFileSyncResource $contentResource
	 */
	protected function attachFileSyncResource(FileAsset $dbFileAsset, vFileSyncResource $contentResource)
	{
    	$syncable = vFileSyncObjectManager::retrieveObject($contentResource->getFileSyncObjectType(), $contentResource->getObjectId());
    	$srcSyncKey = $syncable->getSyncKey($contentResource->getObjectSubType(), $contentResource->getVersion());
    	
        return $this->attachFileSync($dbFileAsset, $srcSyncKey);
    }
    
	/**
	 * @param FileAsset $dbFileAsset
	 * @param string $fullPath
	 * @param bool $copyOnly
	 */
	protected function attachFile(FileAsset $dbFileAsset, $fullPath, $copyOnly = false)
	{
		if(!$dbFileAsset->getFileExt())
		{
			$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
			$dbFileAsset->setFileExt($ext);
		}
		$dbFileAsset->setSize(vFile::fileSize($fullPath));
		$dbFileAsset->incrementVersion();
		$dbFileAsset->save();
		
		$syncKey = $dbFileAsset->getSyncKey(FileAsset::FILE_SYNC_ASSET);
		
		vFileSyncUtils::moveFromFile($fullPath, $syncKey, true, $copyOnly);
		
		$dbFileAsset->setStatus(FileAssetStatus::READY);
		$dbFileAsset->save();
    }
    
	/**
	 * @param FileAsset $dbFileAsset
	 * @param FileSyncKey $srcSyncKey
	 */
	protected function attachFileSync(FileAsset $dbFileAsset, FileSyncKey $srcSyncKey)
	{
		$dbFileAsset->incrementVersion();
		$dbFileAsset->save();
		
        $newSyncKey = $dbFileAsset->getSyncKey(FileAsset::FILE_SYNC_ASSET);
        vFileSyncUtils::createSyncFileLinkForKey($newSyncKey, $srcSyncKey);
                
        $fileSync = vFileSyncUtils::getLocalFileSyncForKey($newSyncKey, false);
        $fileSync = vFileSyncUtils::resolve($fileSync);
        
		$dbFileAsset->setStatus(FileAssetStatus::READY);
		$dbFileAsset->setSize($fileSync->getFileSize());
		$dbFileAsset->save();
    }
    
	/**
	 * List file assets by filter and pager
	 * 
	 * @action list
	 * @param VidiunFilterPager $filter
	 * @param VidiunFileAssetFilter $pager
	 * @return VidiunFileAssetListResponse
	 * @vsIgnored
	 */
	function listAction(VidiunFileAssetFilter $filter, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunFileAssetFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());   
	}
}
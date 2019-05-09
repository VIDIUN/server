<?php

/**
 * Retrieve information and invoke actions on Thumb Asset
 *
 * @service thumbAsset
 * @package api
 * @subpackage services
 */
class ThumbAssetService extends VidiunAssetService
{
	protected function getEnabledMediaTypes()
	{
		$liveStreamTypes = VidiunPluginManager::getExtendedTypes(entryPeer::OM_CLASS, VidiunEntryType::LIVE_STREAM);
		
		$mediaTypes = array_merge($liveStreamTypes, parent::getEnabledMediaTypes());
		$mediaTypes[] = VidiunEntryType::AUTOMATIC;
		
		$mediaTypes = array_unique($mediaTypes);
		return $mediaTypes;
	}
	
	protected function vidiunNetworkAllowed($actionName)
	{
		if(
			$actionName == 'get' ||
			$actionName == 'list' ||
			$actionName == 'getByEntryId' ||
			$actionName == 'getUrl' ||
			$actionName == 'getWebPlayableByEntryId' ||
			$actionName == 'generateByEntryId' ||
			$actionName == 'regenerate'
			)
		{
			$this->partnerGroup .= ',0';
			return true;
		}
			
		return parent::vidiunNetworkAllowed($actionName);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerRequired()
	 */
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'serve') 
			return false;

		if ($actionName === 'serveByEntryId') 
			return false;
		
		return parent::partnerRequired($actionName);
	}
	
    /**
     * Add thumbnail asset
     *
     * @action add
     * @param string $entryId
     * @param VidiunThumbAsset $thumbAsset
     * @return VidiunThumbAsset
     * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
     * @throws VidiunErrors::THUMB_ASSET_ALREADY_EXISTS
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 * @validateUser entry entryId edit
     */
    function addAction($entryId, VidiunThumbAsset $thumbAsset)
    {
    	$dbEntry = entryPeer::retrieveByPK($entryId);
    	if(!$dbEntry || !in_array($dbEntry->getType(), $this->getEnabledMediaTypes()) || ($dbEntry->getType() == entryType::MEDIA_CLIP && !in_array($dbEntry->getMediaType(), array(VidiunMediaType::VIDEO, VidiunMediaType::AUDIO, VidiunMediaType::LIVE_STREAM_FLASH))))
    		throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
    	if($thumbAsset->thumbParamsId)
    	{
    		$dbThumbAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $thumbAsset->thumbParamsId);
    		if($dbThumbAsset)
    			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ALREADY_EXISTS, $dbThumbAsset->getId(), $thumbAsset->thumbParamsId);
    	}
    	
    	$dbThumbAsset = $thumbAsset->toInsertableObject();
    	/* @var $dbThumbAsset thumbAsset */
    	
		$dbThumbAsset->setEntryId($entryId);
		$dbThumbAsset->setPartnerId($dbEntry->getPartnerId());
		$dbThumbAsset->setStatus(thumbAsset::ASSET_STATUS_QUEUED);
		$dbThumbAsset->save();

		$thumbAsset = VidiunThumbAsset::getInstance($dbThumbAsset, $this->getResponseProfile());
		return $thumbAsset;
    }
    
    /**
     * Update content of thumbnail asset
     *
     * @action setContent
     * @param string $id
     * @param VidiunContentResource $contentResource
     * @return VidiunThumbAsset
     * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 * @validateUser asset::entry id edit 
     */
    function setContentAction($id, VidiunContentResource $contentResource)
    {
   		$dbThumbAsset = assetPeer::retrieveById($id);
   		if (!$dbThumbAsset || !($dbThumbAsset instanceof thumbAsset))
   			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbThumbAsset->getentry();
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbThumbAsset->getEntryId());
			
		
		
   		$previousStatus = $dbThumbAsset->getStatus();
		$contentResource->validateEntry($dbThumbAsset->getentry());
		$contentResource->validateAsset($dbThumbAsset);
		$vContentResource = $contentResource->toObject();
    	$this->attachContentResource($dbThumbAsset, $vContentResource);
		$this->validateContent($dbThumbAsset);
		$contentResource->entryHandled($dbThumbAsset->getentry());
		vEventsManager::raiseEvent(new vObjectDataChangedEvent($dbThumbAsset));
		
    	$newStatuses = array(
    		thumbAsset::ASSET_STATUS_READY,
    		thumbAsset::ASSET_STATUS_VALIDATING,
    		thumbAsset::ASSET_STATUS_TEMP,
    	);
    	
    	if($previousStatus == thumbAsset::ASSET_STATUS_QUEUED && in_array($dbThumbAsset->getStatus(), $newStatuses))
   			vEventsManager::raiseEvent(new vObjectAddedEvent($dbThumbAsset));
   		
		$thumbAssetsCount = assetPeer::countThumbnailsByEntryId($dbThumbAsset->getEntryId());
		
		$defaultThumbKey = $dbEntry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_THUMB);
    		
 		//If the thums has the default tag or the entry is in no content and this is the first thumb
 		if($dbThumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB) || ($dbEntry->getStatus() == VidiunEntryStatus::NO_CONTENT 
 			&& $thumbAssetsCount == 1 && !vFileSyncUtils::fileSync_exists($defaultThumbKey)))
		{
			$this->setAsDefaultAction($dbThumbAsset->getId());
		}
		
		$thumbAsset = VidiunThumbAsset::getInstance($dbThumbAsset, $this->getResponseProfile());
		return $thumbAsset;
    }
	
    /**
     * Update thumbnail asset
     *
     * @action update
     * @param string $id
     * @param VidiunThumbAsset $thumbAsset
     * @return VidiunThumbAsset
     * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
     * @validateUser asset::entry id edit 
     */
    function updateAction($id, VidiunThumbAsset $thumbAsset)
    {
		$dbThumbAsset = assetPeer::retrieveById($id);
		if (!$dbThumbAsset || !($dbThumbAsset instanceof thumbAsset))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbThumbAsset->getentry();
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbThumbAsset->getEntryId());
			
		
		
    	$dbThumbAsset = $thumbAsset->toUpdatableObject($dbThumbAsset);
   		$dbThumbAsset->save();
		
		if($dbEntry->getCreateThumb() && $dbThumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB))
			$this->setAsDefaultAction($dbThumbAsset->getId());
			
		$thumbAsset = VidiunThumbAsset::getInstance($dbThumbAsset, $this->getResponseProfile());
		return $thumbAsset;
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param string $fullPath
	 * @param bool $copyOnly
	 */
	protected function attachFile(thumbAsset $thumbAsset, $fullPath, $copyOnly = false)
	{
		$filePath = parse_url($fullPath,PHP_URL_PATH);
		$ext = pathinfo($filePath,PATHINFO_EXTENSION);

		$thumbAsset->incrementVersion();
		$thumbAsset->setFileExt($ext);
		$thumbAsset->setSize(filesize($fullPath));
		$thumbAsset->save();
		
		$syncKey = $thumbAsset->getSyncKey(thumbAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		
		try {
			vFileSyncUtils::moveFromFile($fullPath, $syncKey, true, $copyOnly);
		}
		catch (Exception $e) {
			
			if($thumbAsset->getStatus() == thumbAsset::ASSET_STATUS_QUEUED || $thumbAsset->getStatus() == thumbAsset::ASSET_STATUS_NOT_APPLICABLE)
			{
				$thumbAsset->setDescription($e->getMessage());
				$thumbAsset->setStatus(thumbAsset::ASSET_STATUS_ERROR);
				$thumbAsset->save();
			}												
			throw $e;
		}
		
		$fileSync = vFileSyncUtils::getLocalFileSyncForKey($syncKey, false);
		list($width, $height, $type, $attr) = vImageUtils::getImageSize($fileSync);
		
		$thumbAsset->setWidth($width);
		$thumbAsset->setHeight($height);
		$thumbAsset->setSize($fileSync->getFileSize());
		
		$thumbAsset->setStatusLocalReady();
		$thumbAsset->save();
	}
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param string $url
	 */
	protected function attachUrl(thumbAsset $thumbAsset, $url)
	{
    	$fullPath = myContentStorage::getFSUploadsPath() . '/' . $thumbAsset->getId() . '.jpg';
		if (VCurlWrapper::getDataFromFile($url, $fullPath))
			return $this->attachFile($thumbAsset, $fullPath);
			
		if($thumbAsset->getStatus() == thumbAsset::ASSET_STATUS_QUEUED || $thumbAsset->getStatus() == thumbAsset::ASSET_STATUS_NOT_APPLICABLE)
		{
			$thumbAsset->setDescription("Failed downloading file[$url]");
			$thumbAsset->setStatus(thumbAsset::ASSET_STATUS_ERROR);
			$thumbAsset->save();
		}
		
		throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_DOWNLOAD_FAILED, $url);
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param vUrlResource $contentResource
	 */
	protected function attachUrlResource(thumbAsset $thumbAsset, vUrlResource $contentResource)
	{
    	$this->attachUrl($thumbAsset, $contentResource->getUrl());
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param vLocalFileResource $contentResource
	 */
	protected function attachLocalFileResource(thumbAsset $thumbAsset, vLocalFileResource $contentResource)
	{
		if($contentResource->getIsReady())
			return $this->attachFile($thumbAsset, $contentResource->getLocalFilePath(), $contentResource->getKeepOriginalFile());
			
		$thumbAsset->setStatus(asset::ASSET_STATUS_IMPORTING);
		$thumbAsset->save();
		
		$contentResource->attachCreatedObject($thumbAsset);
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param FileSyncKey $srcSyncKey
	 */
	protected function attachFileSync(thumbAsset $thumbAsset, FileSyncKey $srcSyncKey)
	{
		$thumbAsset->incrementVersion();
		$thumbAsset->save();
		
        $newSyncKey = $thumbAsset->getSyncKey(thumbAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
        vFileSyncUtils::createSyncFileLinkForKey($newSyncKey, $srcSyncKey);
		
		$fileSync = vFileSyncUtils::getLocalFileSyncForKey($newSyncKey, false);
		list($width, $height, $type, $attr) = vImageUtils::getImageSize($fileSync);
		
		$thumbAsset->setWidth($width);
		$thumbAsset->setHeight($height);
		$thumbAsset->setSize($fileSync->getFileSize());
		
		$thumbAsset->setStatusLocalReady();
		$thumbAsset->save();
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param vFileSyncResource $contentResource
	 */
	protected function attachFileSyncResource(thumbAsset $thumbAsset, vFileSyncResource $contentResource)
	{
    	$syncable = vFileSyncObjectManager::retrieveObject($contentResource->getFileSyncObjectType(), $contentResource->getObjectId());
    	$srcSyncKey = $syncable->getSyncKey($contentResource->getObjectSubType(), $contentResource->getVersion());
    	
        return $this->attachFileSync($thumbAsset, $srcSyncKey);
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param IRemoteStorageResource $contentResource
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 */
	protected function attachRemoteStorageResource(thumbAsset $thumbAsset, IRemoteStorageResource $contentResource)
	{
		$resources = $contentResource->getResources();
		
		$thumbAsset->setFileExt($contentResource->getFileExt());
        $thumbAsset->incrementVersion();
		$thumbAsset->setStatusLocalReady();
        $thumbAsset->save();
        	
        $syncKey = $thumbAsset->getSyncKey(thumbAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		foreach($resources as $currentResource)
		{
			$storageProfile = StorageProfilePeer::retrieveByPK($currentResource->getStorageProfileId());
			$fileSync = vFileSyncUtils::createReadyExternalSyncFileForKey($syncKey, $currentResource->getUrl(), $storageProfile);
		}
    }
    
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param vContentResource $contentResource
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 */
	protected function attachContentResource(thumbAsset $thumbAsset, vContentResource $contentResource)
	{
    	switch($contentResource->getType())
    	{
			case 'vUrlResource':
				return $this->attachUrlResource($thumbAsset, $contentResource);
				
			case 'vLocalFileResource':
				return $this->attachLocalFileResource($thumbAsset, $contentResource);
				
			case 'vFileSyncResource':
				return $this->attachFileSyncResource($thumbAsset, $contentResource);
				
			case 'vRemoteStorageResource':
			case 'vRemoteStorageResources':
				return $this->attachRemoteStorageResource($thumbAsset, $contentResource);
				
			default:
				$msg = "Resource of type [" . get_class($contentResource) . "] is not supported";
				VidiunLog::err($msg);
				
				if($thumbAsset->getStatus() == thumbAsset::ASSET_STATUS_QUEUED || $thumbAsset->getStatus() == thumbAsset::ASSET_STATUS_NOT_APPLICABLE)
				{
					$thumbAsset->setDescription($msg);
					$thumbAsset->setStatus(asset::ASSET_STATUS_ERROR);
					$thumbAsset->save();
				}
				
				throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($contentResource));
    	}
    }
    
    
	/**
	 * Serves thumbnail by entry id and thumnail params id
	 *  
	 * @action serveByEntryId
	 * @param string $entryId
	 * @param int $thumbParamId if not set, default thumbnail will be used.
	 * @return file
	 * @vsOptional
	 * 
	 * @throws VidiunErrors::THUMB_ASSET_IS_NOT_READY
	 * @throws VidiunErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function serveByEntryIdAction($entryId, $thumbParamId = null)
	{
		$entry = null;
		if (!vCurrentContext::$vs)
		{
			$entry = vCurrentContext::initPartnerByEntryId($entryId);
			
			if (!$entry || $entry->getStatus() == entryStatus::DELETED)
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
				
			// enforce entitlement
			$this->setPartnerFilters(vCurrentContext::getCurrentPartnerId());
			vEntitlementUtils::initEntitlementEnforcement();
			
			if(!vEntitlementUtils::isEntryEntitled($entry))
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);				
		}
		else 
		{	
			$entry = entryPeer::retrieveByPK($entryId);
		}
		
		if (!$entry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$securyEntryHelper = new VSecureEntryHelper($entry, vCurrentContext::$vs, null, ContextType::THUMBNAIL);
		$securyEntryHelper->validateAccessControl();
		
		$fileName = $entry->getId() . '.jpg';
		
		if(is_null($thumbParamId))
			return $this->serveFile($entry, entry::FILE_SYNC_ENTRY_SUB_TYPE_THUMB, $fileName, $entryId);
		
		$thumbAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $thumbParamId);
		if(!$thumbAsset)
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND, $thumbParamId);
		
		return $this->serveAsset($thumbAsset, $fileName);
	}

	/**
	 * Serves thumbnail by its id
	 *  
	 * @action serve
	 * @param string $thumbAssetId
	 * @param int $version
	 * @param VidiunThumbParams $thumbParams
	 * @param VidiunThumbnailServeOptions $options
	 * @return file
	 * @vsOptional
	 *  
	 * @throws VidiunErrors::THUMB_ASSET_IS_NOT_READY
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 */
	public function serveAction($thumbAssetId, $version = null, VidiunThumbParams $thumbParams = null, VidiunThumbnailServeOptions $options = null)
	{
		if (!vCurrentContext::$vs)
		{
			$thumbAsset = vCurrentContext::initPartnerByAssetId($thumbAssetId);
			
			if (!$thumbAsset || $thumbAsset->getStatus() == asset::ASSET_STATUS_DELETED)
				throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
				
			// enforce entitlement
			$this->setPartnerFilters(vCurrentContext::getCurrentPartnerId());
			vEntitlementUtils::initEntitlementEnforcement();
		}
		else 
		{	
			$thumbAsset = assetPeer::retrieveById($thumbAssetId);
		}
			
		if (!$thumbAsset || !($thumbAsset instanceof thumbAsset))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
			
		$entry = entryPeer::retrieveByPK($thumbAsset->getEntryId());
		if(!$entry)
		{
			//we will throw thumb asset not found, as the user is not entitled, and should not know that the entry exists.
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
		}

		$referrer = null;
		if($options && $options->referrer)
			$referrer = $options->referrer;

		$securyEntryHelper = new VSecureEntryHelper($entry, vCurrentContext::$vs, $referrer, ContextType::THUMBNAIL);
		$securyEntryHelper->validateAccessControl();

		$ext = $thumbAsset->getFileExt();
		if(is_null($ext))
			$ext = 'jpg';
			
		$fileName = $thumbAsset->getEntryId()."_" . $thumbAsset->getId() . ".$ext";
		if(!$thumbParams)
		{
			if($options && $options->download)
				header("Content-Disposition: attachment; filename=\"$fileName\"");
			return $this->serveAsset($thumbAsset, $fileName, false, $version);
		}
			
		$thumbParams->validate();
		
		$syncKey = $thumbAsset->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET, $version);
		if(!vFileSyncUtils::fileSync_exists($syncKey))
			throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);
			
		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
		/* @var $fileSync FileSync */
		
		if(!$local)
		{
			if ( !in_array($fileSync->getDc(), vDataCenterMgr::getDcIds()) )
				throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);
				
			$remoteUrl = vDataCenterMgr::getRedirectExternalUrl($fileSync);
			VidiunLog::info("Redirecting to [$remoteUrl]");
			header("Location: $remoteUrl");
			die;
		}
		
		$filePath = $fileSync->getFullPath();
		
		$thumbVersion = $thumbAsset->getId() . '_' . $version;
		$tempThumbPath = myEntryUtils::resizeEntryImage($entry, $thumbVersion, 
			$thumbParams->width, 
			$thumbParams->height, 
			$thumbParams->cropType, 
			$thumbParams->backgroundColor, 
			null, 
			$thumbParams->quality,
			$thumbParams->cropX, 
			$thumbParams->cropY, 
			$thumbParams->cropWidth, 
			$thumbParams->cropHeight, 
			-1, 0, -1, 
			$filePath, 
			$thumbParams->density, 
			$thumbParams->stripProfiles, 
			null, null,
			$fileSync);
		
		if($options && $options->download)
			header("Content-Disposition: attachment; filename=\"$fileName\"");
			
		$mimeType = vFile::mimeType($tempThumbPath);
		$key = vFileUtils::isFileEncrypt($tempThumbPath) ? $entry->getGeneralEncryptionKey() : null;
		$iv = $key ? $entry->getEncryptionIv() : null;
		return $this->dumpFile($tempThumbPath, $mimeType, $key, $iv);
	}
	
	/**
	 * Tags the thumbnail as DEFAULT_THUMB and removes that tag from all other thumbnail assets of the entry.
	 * Create a new file sync link on the entry thumbnail that points to the thumbnail asset file sync.
	 *  
	 * @action setAsDefault
	 * @param string $thumbAssetId
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @validateUser asset::entry thumbAssetId edit 
	 */
	public function setAsDefaultAction($thumbAssetId)
	{
		$thumbAsset = assetPeer::retrieveById($thumbAssetId);
		if (!$thumbAsset || !($thumbAsset instanceof thumbAsset))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
		
		vBusinessConvertDL::setAsDefaultThumbAsset($thumbAsset);
	}

	/**
	 * @action generateByEntryId
	 * @param string $entryId
	 * @param int $destThumbParamsId indicate the id of the ThumbParams to be generate this thumbnail by
	 * @return VidiunThumbAsset
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED
	 * @throws VidiunErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED
	 * @throws VidiunErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND
	 * @throws VidiunErrors::INVALID_ENTRY_STATUS
	 * @throws VidiunErrors::THUMB_ASSET_IS_NOT_READY
	 * @validateUser entry entryId edit
	 */
	public function generateByEntryIdAction($entryId, $destThumbParamsId)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if(!$entry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		if (!in_array($entry->getType(), $this->getEnabledMediaTypes()))
			throw new VidiunAPIException(VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED, $entry->getType());
		if ($entry->getMediaType() != entry::ENTRY_MEDIA_TYPE_VIDEO)
			throw new VidiunAPIException(VidiunErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED, $entry->getMediaType());
						
		
			
		$validStatuses = array(
			entryStatus::ERROR_CONVERTING,
			entryStatus::PRECONVERT,
			entryStatus::READY,
		);
		
		if (!in_array($entry->getStatus(), $validStatuses))
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_STATUS);
			
		$destThumbParams = assetParamsPeer::retrieveByPK($destThumbParamsId);
		if(!$destThumbParams)
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND, $destThumbParamsId);

		myEntryUtils::verifyThumbSrcExist($entry, $destThumbParams);

		$dbThumbAsset = vBusinessPreConvertDL::decideThumbGenerate($entry, $destThumbParams);
		if(!$dbThumbAsset)
			return null;
			
		$thumbAsset = new VidiunThumbAsset();
		$thumbAsset->fromObject($dbThumbAsset, $this->getResponseProfile());
		return $thumbAsset;
	}

	/**
	 * @action generate
	 * @param string $entryId
	 * @param VidiunThumbParams $thumbParams
	 * @param string $sourceAssetId id of the source asset (flavor or thumbnail) to be used as source for the thumbnail generation
	 * @return VidiunThumbAsset
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED
	 * @throws VidiunErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED
	 * @throws VidiunErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND
	 * @throws VidiunErrors::INVALID_ENTRY_STATUS
	 * @throws VidiunErrors::THUMB_ASSET_IS_NOT_READY
	 * @validateUser entry entryId edit
	 */
	public function generateAction($entryId, VidiunThumbParams $thumbParams, $sourceAssetId = null)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if(!$entry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		if (!in_array($entry->getType(), $this->getEnabledMediaTypes()))
			throw new VidiunAPIException(VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED, $entry->getType());
			
		if ($entry->getMediaType() != entry::ENTRY_MEDIA_TYPE_VIDEO)
			throw new VidiunAPIException(VidiunErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED, $entry->getMediaType());
			
		
		
		$validStatuses = array(
			entryStatus::ERROR_CONVERTING,
			entryStatus::PRECONVERT,
			entryStatus::READY,
		);
		
		if (!in_array($entry->getStatus(), $validStatuses))
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_STATUS);
			
		$destThumbParams = new thumbParams();
		$thumbParams->toUpdatableObject($destThumbParams);

		$srcAsset = vBusinessPreConvertDL::getSourceAssetForGenerateThumbnail($sourceAssetId, $destThumbParams->getSourceParamsId(), $entryId);		
		if (is_null($srcAsset))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_IS_NOT_READY);
		
		$sourceFileSyncKey = $srcAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET); 
		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($sourceFileSyncKey,true);
		/* @var $fileSync FileSync */
		
		if(is_null($fileSync))
		{
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_IS_NOT_READY);
		}
		
		if($fileSync->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL)
		{
			throw new VidiunAPIException(VidiunErrors::SOURCE_FILE_REMOTE);
		}
		
		if(!$local)
		{
			vFileUtils::dumpApiRequest(vDataCenterMgr::getRemoteDcExternalUrl($fileSync));
		}
		
		$dbThumbAsset = vBusinessPreConvertDL::decideThumbGenerate($entry, $destThumbParams, null, $sourceAssetId, true , $srcAsset);
		if(!$dbThumbAsset)
			return null;
			
		$thumbAsset = new VidiunThumbAsset();
		$thumbAsset->fromObject($dbThumbAsset, $this->getResponseProfile());
		return $thumbAsset;
	}

	/**
	 * @action regenerate
	 * @param string $thumbAssetId
	 * @return VidiunThumbAsset
	 * 
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED
	 * @throws VidiunErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED
	 * @throws VidiunErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND
	 * @throws VidiunErrors::INVALID_ENTRY_STATUS
	 * @validateUser asset::entry thumbAssetId edit
	 */
	public function regenerateAction($thumbAssetId)
	{
		$thumbAsset = assetPeer::retrieveById($thumbAssetId);
		if (!$thumbAsset || !($thumbAsset instanceof thumbAsset))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
			
		if(is_null($thumbAsset->getFlavorParamsId()))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND, null);
			
		$destThumbParams = assetParamsPeer::retrieveByPK($thumbAsset->getFlavorParamsId());
		if(!$destThumbParams)
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND, $thumbAsset->getFlavorParamsId());
			
		$entry = $thumbAsset->getentry();
		if (!in_array($entry->getType(), $this->getEnabledMediaTypes()))
			throw new VidiunAPIException(VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED, $entry->getType());
		if ($entry->getMediaType() != entry::ENTRY_MEDIA_TYPE_VIDEO)
			throw new VidiunAPIException(VidiunErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED, $entry->getMediaType());
						
		
			
		$validStatuses = array(
			entryStatus::ERROR_CONVERTING,
			entryStatus::PRECONVERT,
			entryStatus::READY,
		);
		
		if (!in_array($entry->getStatus(), $validStatuses))
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_STATUS);

		myEntryUtils::verifyThumbSrcExist($entry, $destThumbParams);

		$dbThumbAsset = vBusinessPreConvertDL::decideThumbGenerate($entry, $destThumbParams);
		if(!$dbThumbAsset)
			return null;
			
		$thumbAsset = new VidiunThumbAsset();
		$thumbAsset->fromObject($dbThumbAsset, $this->getResponseProfile());
		return $thumbAsset;
	}
	
	/**
	 * @action get
	 * @param string $thumbAssetId
	 * @return VidiunThumbAsset
	 * 
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 */
	public function getAction($thumbAssetId)
	{
		$thumbAssetsDb = assetPeer::retrieveById($thumbAssetId);
		if (!$thumbAssetsDb || !($thumbAssetsDb instanceof thumbAsset))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
			
		if(vEntitlementUtils::getEntitlementEnforcement())
		{
			$entry = entryPeer::retrieveByPK($thumbAssetsDb->getEntryId());
			if(!$entry)
			{
				//we will throw thumb asset not found, as the user is not entitled, and should not know that the entry exists.
				throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
			}	
		}
		
		$thumbAssets = VidiunThumbAsset::getInstance($thumbAssetsDb, $this->getResponseProfile());
		return $thumbAssets;
	}
	
	/**
	 * @action getByEntryId
	 * @param string $entryId
	 * @return VidiunThumbAssetArray
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @deprecated Use thumbAsset.list instead
	 */
	public function getByEntryIdAction($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		// get the thumb assets for this entry
		$c = new Criteria();
		$c->add(assetPeer::ENTRY_ID, $entryId);
		
		//VMC currently does not support showing thumb asset extending types
		//$thumbTypes = VidiunPluginManager::getExtendedTypes(assetPeer::OM_CLASS, assetType::THUMBNAIL);
		//$c->add(assetPeer::TYPE, $thumbTypes, Criteria::IN);
		
		$c->add(assetPeer::TYPE, assetType::THUMBNAIL, Criteria::EQUAL);
		
		$thumbAssetsDb = assetPeer::doSelect($c);
		$thumbAssets = VidiunThumbAssetArray::fromDbArray($thumbAssetsDb, $this->getResponseProfile());
		return $thumbAssets;
	}
	
	/**
	 * List Thumbnail Assets by filter and pager
	 * 
	 * @action list
	 * @param VidiunAssetFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunThumbAssetListResponse
	 */
	function listAction(VidiunAssetFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
		{
			$filter = new VidiunThumbAssetFilter();
		}
		elseif(! $filter instanceof VidiunThumbAssetFilter)
		{
                        if(!is_subclass_of('VidiunThumbAssetFilter', get_class($filter)))
                            $filter = $filter->cast('VidiunAssetFilter');
		    
			$filter = $filter->cast('VidiunThumbAssetFilter');
		}
			
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}
			
		$types = VidiunPluginManager::getExtendedTypes(assetPeer::OM_CLASS, assetType::THUMBNAIL);
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $types);
	}
	
	/**
	 * @action addFromUrl
	 * @param string $entryId
	 * @param string $url
	 * @return VidiunThumbAsset
	 * 
	 * @deprecated use thumbAsset.add and thumbAsset.setContent instead
	 */
	public function addFromUrlAction($entryId, $url)
	{

		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$res = VCurlWrapper::getContent($url);
		if (!$res)
		{
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_DOWNLOAD_FAILED, $url);
		}
		
		$ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
		
		$dbThumbAsset = new thumbAsset();
		$dbThumbAsset->setPartnerId($dbEntry->getPartnerId());
		$dbThumbAsset->setEntryId($dbEntry->getId());
		$dbThumbAsset->setStatus(thumbAsset::ASSET_STATUS_QUEUED);
		$dbThumbAsset->setFileExt($ext);
		$dbThumbAsset->incrementVersion();
		$dbThumbAsset->save();
		
		$syncKey = $dbThumbAsset->getSyncKey(thumbAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);

		vFileSyncUtils::file_put_contents($syncKey, $res);
		
		/* @var $fileSync FileSync */
		$fileSync = vFileSyncUtils::getLocalFileSyncForKey($syncKey, false);
		list($width, $height, $type, $attr) = vImageUtils::getImageSize($fileSync);
		$this->validateContent($dbThumbAsset);

		$dbThumbAsset->setWidth($width);
		$dbThumbAsset->setHeight($height);
		$dbThumbAsset->setSize($fileSync->getFileSize());
		$dbThumbAsset->setStatusLocalReady();
		$dbThumbAsset->save();
		
		$thumbAssets = new VidiunThumbAsset();
		$thumbAssets->fromObject($dbThumbAsset, $this->getResponseProfile());
		return $thumbAssets;
	}
	
	/**
	 * @action addFromImage
	 * @param string $entryId
	 * @param file $fileData
	 * @return VidiunThumbAsset
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry entryId edit
	 */
	public function addFromImageAction($entryId, $fileData)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		
		
		$ext = pathinfo($fileData["name"], PATHINFO_EXTENSION);
		
		$dbThumbAsset = new thumbAsset();
		$dbThumbAsset->setPartnerId($dbEntry->getPartnerId());
		$dbThumbAsset->setEntryId($dbEntry->getId());
		$dbThumbAsset->setStatus(thumbAsset::ASSET_STATUS_QUEUED);
		$dbThumbAsset->setFileExt($ext);
		$dbThumbAsset->incrementVersion();
		$dbThumbAsset->save();
		
		$syncKey = $dbThumbAsset->getSyncKey(thumbAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);

		//extract the data before moving the file in case of encryption
		list($width, $height, $type, $attr) = getimagesize($fileData["tmp_name"]);
		$fileSize = vFileBase::fileSize($fileData["tmp_name"]);

		vFileSyncUtils::moveFromFile($fileData["tmp_name"], $syncKey);

		$this->validateContent($dbThumbAsset);
		$dbThumbAsset->setWidth($width);
		$dbThumbAsset->setHeight($height);
		$dbThumbAsset->setSize($fileSize);
		$dbThumbAsset->setStatusLocalReady();
		$dbThumbAsset->save();

		$dbEntryThumbs = assetPeer::retrieveThumbnailsByEntryId($entryId);
    		
 		//If the thums has the default tag or the entry is in no content and this is the first thumb
		if($dbEntry->getCreateThumb() && 
			(
				$dbThumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB) || 
		  		($dbEntry->getStatus() == VidiunEntryStatus::NO_CONTENT && count($dbEntryThumbs) == 1)
		  	)
		  )
				$this->setAsDefaultAction($dbThumbAsset->getId());
			
		$thumbAssets = new VidiunThumbAsset();
		$thumbAssets->fromObject($dbThumbAsset, $this->getResponseProfile());
		return $thumbAssets;
	}
	
	/**
	 * @action delete
	 * @param string $thumbAssetId
	 * 
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @validateUser asset::entry thumbAssetId edit
	 */
	public function deleteAction($thumbAssetId)
	{
		$thumbAssetDb = assetPeer::retrieveById($thumbAssetId);
		if (!$thumbAssetDb || !($thumbAssetDb instanceof thumbAsset))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);

		if(vEntitlementUtils::getEntitlementEnforcement())
		{
			$entry = entryPeer::retrieveByPK($thumbAssetDb->getEntryId());
			if(!$entry)
			{
				//we will throw thumb asset not found, as the user is not entitled, and should not know that the entry exists.
				throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
			}	
		}
			
		if($thumbAssetDb->hasTag(thumbParams::TAG_DEFAULT_THUMB))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_IS_DEFAULT, $thumbAssetId);
		
		$entry = $thumbAssetDb->getEntry();
		if (!$entry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $thumbAssetDb->getEntryId());
			
		
		
		$thumbAssetDb->setStatus(thumbAsset::ASSET_STATUS_DELETED);
		$thumbAssetDb->setDeletedAt(time());
		$thumbAssetDb->save();
	}
	
	/**
	 * Get download URL for the asset
	 * 
	 * @action getUrl
	 * @param string $id
	 * @param int $storageId
	 * @param VidiunThumbParams $thumbParams
	 * @return string
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::THUMB_ASSET_IS_NOT_READY
	 */
	public function getUrlAction($id, $storageId = null, VidiunThumbParams $thumbParams = null)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof thumbAsset))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $id);

		$entry = entryPeer::retrieveByPK($assetDb->getEntryId());
		if(!$entry)
		{
			//we will throw thumb asset not found, as the user is not entitled, and should not know that the entry exists or entry does not exist.
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $id);
		}

		if ($assetDb->getStatus() != asset::ASSET_STATUS_READY)
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_IS_NOT_READY);
		
		$securyEntryHelper = new VSecureEntryHelper($entry, vCurrentContext::$vs, null, ContextType::THUMBNAIL);
		$securyEntryHelper->validateAccessControl();
		
		return $assetDb->getThumbnailUrl($securyEntryHelper, $storageId, $thumbParams);
	}
		
	/**
	 * Get remote storage existing paths for the asset
	 * 
	 * @action getRemotePaths
	 * @param string $id
	 * @return VidiunRemotePathListResponse
	 * @throws VidiunErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::THUMB_ASSET_IS_NOT_READY
	 */
	public function getRemotePathsAction($id)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof thumbAsset))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $id);
			
		if(vEntitlementUtils::getEntitlementEnforcement())
		{
			$entry = entryPeer::retrieveByPK($assetDb->getEntryId());
			if(!$entry)
			{
				//we will throw thumb asset not found, as the user is not entitled, and should not know that the entry exists.
				throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_NOT_FOUND, $id);
			}	
		}

		if ($assetDb->getStatus() != asset::ASSET_STATUS_READY)
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_IS_NOT_READY);

		$c = new Criteria();
		$c->add(FileSyncPeer::OBJECT_TYPE, FileSyncObjectType::ASSET);
		$c->add(FileSyncPeer::OBJECT_SUB_TYPE, asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		$c->add(FileSyncPeer::OBJECT_ID, $id);
		$c->add(FileSyncPeer::VERSION, $assetDb->getVersion());
		$c->add(FileSyncPeer::PARTNER_ID, $assetDb->getPartnerId());
		$c->add(FileSyncPeer::STATUS, FileSync::FILE_SYNC_STATUS_READY);
		$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_URL);
		$fileSyncs = FileSyncPeer::doSelect($c);
			
		$listResponse = new VidiunRemotePathListResponse();
		$listResponse->objects = VidiunRemotePathArray::fromDbArray($fileSyncs, $this->getResponseProfile());
		$listResponse->totalCount = count($listResponse->objects);
		return $listResponse;
	}

	/**
	 * manually export an asset
	 *
	 * @action export
	 * @param string $assetId
	 * @param int $storageProfileId
	 * @throws VidiunErrors::INVALID_FLAVOR_ASSET_ID
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::INTERNAL_SERVERL_ERROR
	 * @return VidiunFlavorAsset The exported asset
	 */
	public function exportAction ( $assetId , $storageProfileId )
	{
		return parent::exportAction($assetId, $storageProfileId);
	}

	protected function validateContent($dbThumbAsset)
	{
		try
		{
			myEntryUtils::validateObjectContent($dbThumbAsset);
		}
		catch (Exception $e)
		{
			$dbThumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_ERROR);
			$dbThumbAsset->save();
			throw new VidiunAPIException(VidiunErrors::IMAGE_CONTENT_NOT_SECURE);
		}
	}
	
}

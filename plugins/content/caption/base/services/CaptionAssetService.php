<?php

/**
 * Retrieve information and invoke actions on caption Asset
 *
 * @service captionAsset
 * @package plugins.caption
 * @subpackage api.services
 */
class CaptionAssetService extends VidiunAssetService
{
	const MAX_SERVE_WEBVTT_FILE_SIZE = 1048576;

	protected function vidiunNetworkAllowed($actionName)
	{
		if (
			$actionName == 'get' ||
			$actionName == 'list' ||
			$actionName == 'getUrl'
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
	 * Add caption asset
	 *
	 * @action add
	 * @param string $entryId
	 * @param VidiunCaptionAsset $captionAsset
	 * @return VidiunCaptionAsset
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ALREADY_EXISTS
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 * @validateUser entry entryId edit
	 */
	function addAction($entryId, VidiunCaptionAsset $captionAsset)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry || !in_array($dbEntry->getType(), $this->getEnabledMediaTypes()) || !in_array($dbEntry->getMediaType(), array(VidiunMediaType::VIDEO, VidiunMediaType::AUDIO)))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ($captionAsset->captionParamsId)
		{
			$dbCaptionAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $captionAsset->captionParamsId);
			if ($dbCaptionAsset)
				throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ALREADY_EXISTS, $dbCaptionAsset->getId(), $captionAsset->captionParamsId);
		}

		$dbCaptionAsset = new CaptionAsset();
		$dbCaptionAsset = $captionAsset->toInsertableObject($dbCaptionAsset);
		
		if($this->getVs() && $this->getVs()->getPrivilegeByName(VSessionBase::PRIVILEGE_ENABLE_CAPTION_MODERATION))
			$dbCaptionAsset->setDisplayOnPlayer(false);
				
		$dbCaptionAsset->setEntryId($entryId);
		$dbCaptionAsset->setPartnerId($dbEntry->getPartnerId());
		$dbCaptionAsset->setStatus(CaptionAsset::ASSET_STATUS_QUEUED);
		$dbCaptionAsset->save();

		$captionAsset = new VidiunCaptionAsset();
		$captionAsset->fromObject($dbCaptionAsset, $this->getResponseProfile());
		return $captionAsset;
	}

	/**
	 * Update content of caption asset
	 *
	 * @action setContent
	 * @param string $id
	 * @param VidiunContentResource $contentResource
	 * @return VidiunCaptionAsset
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 * @validateUser asset::entry id edit
	 */
	function setContentAction($id, VidiunContentResource $contentResource)
	{
		$dbCaptionAsset = assetPeer::retrieveById($id);
		if (!$dbCaptionAsset || !($dbCaptionAsset instanceof CaptionAsset))
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $id);

		$dbEntry = $dbCaptionAsset->getentry();
		if (!$dbEntry || !in_array($dbEntry->getType(), $this->getEnabledMediaTypes()) || !in_array($dbEntry->getMediaType(), array(VidiunMediaType::VIDEO, VidiunMediaType::AUDIO)))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbCaptionAsset->getEntryId());


		$previousStatus = $dbCaptionAsset->getStatus();
		$contentResource->validateEntry($dbCaptionAsset->getentry());
		$contentResource->validateAsset($dbCaptionAsset);
		$vContentResource = $contentResource->toObject();
		$this->attachContentResource($dbCaptionAsset, $vContentResource);
		$contentResource->entryHandled($dbCaptionAsset->getentry());
		
    	$newStatuses = array(
    		CaptionAsset::ASSET_STATUS_READY,
    		CaptionAsset::ASSET_STATUS_VALIDATING,
    		CaptionAsset::ASSET_STATUS_TEMP,
    	);
    	
    	if($previousStatus == CaptionAsset::ASSET_STATUS_QUEUED && in_array($dbCaptionAsset->getStatus(), $newStatuses))
   			vEventsManager::raiseEvent(new vObjectAddedEvent($dbCaptionAsset));
   		else
	    {
		    vEventsManager::raiseEvent(new vObjectDataChangedEvent($dbCaptionAsset));
		    $dbEntry->setCacheFlavorVersion($dbEntry->getCacheFlavorVersion() + 1);
		    $dbEntry->save();
	    }

		$captionAsset = new VidiunCaptionAsset();
		$captionAsset->fromObject($dbCaptionAsset, $this->getResponseProfile());
		return $captionAsset;
	}

	/**
	 * Update caption asset
	 *
	 * @action update
	 * @param string $id
	 * @param VidiunCaptionAsset $captionAsset
	 * @return VidiunCaptionAsset
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser asset::entry id edit
	 */
	function updateAction($id, VidiunCaptionAsset $captionAsset)
	{
		$dbCaptionAsset = assetPeer::retrieveById($id);
		if (!$dbCaptionAsset || !($dbCaptionAsset instanceof CaptionAsset))
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $id);

		$dbEntry = $dbCaptionAsset->getentry();
		if (!$dbEntry || !in_array($dbEntry->getType(), $this->getEnabledMediaTypes()) || !in_array($dbEntry->getMediaType(), array(VidiunMediaType::VIDEO, VidiunMediaType::AUDIO)))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbCaptionAsset->getEntryId());


		$dbCaptionAsset = $captionAsset->toUpdatableObject($dbCaptionAsset);
		$dbCaptionAsset->save();

		if ($dbCaptionAsset->getDefault())
			$this->setAsDefaultAction($dbCaptionAsset->getId());

		$captionAsset = new VidiunCaptionAsset();
		$captionAsset->fromObject($dbCaptionAsset, $this->getResponseProfile());
		return $captionAsset;
	}

	/**
	 * @param CaptionAsset $captionAsset
	 * @param string $fullPath
	 * @param bool $copyOnly
	 */
	protected function attachFile(CaptionAsset $captionAsset, $fullPath, $copyOnly = false)
	{
		$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
		list($width, $height, $type, $attr) = getimagesize($fullPath);

		$captionAsset->incrementVersion();
		if ($ext && $ext != vUploadTokenMgr::NO_EXTENSION_IDENTIFIER)
			$captionAsset->setFileExt($ext);

		$captionAsset->setSize(filesize($fullPath));
		$captionAsset->save();

		$syncKey = $captionAsset->getSyncKey(CaptionAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);

		try
		{
			vFileSyncUtils::moveFromFile($fullPath, $syncKey, true, $copyOnly);
		} catch (Exception $e)
		{

			if ($captionAsset->getStatus() == CaptionAsset::ASSET_STATUS_QUEUED || $captionAsset->getStatus() == CaptionAsset::ASSET_STATUS_NOT_APPLICABLE)
			{
				$captionAsset->setDescription($e->getMessage());
				$captionAsset->setStatus(CaptionAsset::ASSET_STATUS_ERROR);
				$captionAsset->save();
			}
			throw $e;
		}


		$fileSync = vFileSyncUtils::getLocalFileSyncForKey($syncKey);
		$finalPath = $fileSync->getFullPath();

		if ($captionAsset->getLanguage() == VidiunLanguage::MU)
		{
			vCaptionsContentManager::addParseMultiLanguageCaptionAssetJob($captionAsset, $finalPath, $fileSync->getEncryptionKey());
		}

		$captionAsset->setWidth($width);
		$captionAsset->setHeight($height);
		$captionAsset->setSize($fileSync->getFileSize());
		$captionAsset->setStatus(CaptionAsset::ASSET_STATUS_READY);
		$captionAsset->save();
	}

	/**
	 * @param CaptionAsset $captionAsset
	 * @param string $url
	 */
	protected function attachUrl(CaptionAsset $captionAsset, $url)
	{
		$destPath = md5($url);
		$fullPath = myContentStorage::getFSUploadsPath() . '/' . $destPath;
		if (VCurlWrapper::getDataFromFile($url, $fullPath))
			return $this->attachFile($captionAsset, $fullPath);

		if ($captionAsset->getStatus() == CaptionAsset::ASSET_STATUS_QUEUED || $captionAsset->getStatus() == CaptionAsset::ASSET_STATUS_NOT_APPLICABLE)
		{
			$captionAsset->setDescription("Failed downloading file[$url]");
			$captionAsset->setStatus(CaptionAsset::ASSET_STATUS_ERROR);
			$captionAsset->save();
		}

		throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_DOWNLOAD_FAILED, $url);
	}

	/**
	 * @param CaptionAsset $captionAsset
	 * @param vUrlResource $contentResource
	 */
	protected function attachUrlResource(CaptionAsset $captionAsset, vUrlResource $contentResource)
	{
		$this->attachUrl($captionAsset, $contentResource->getUrl());
	}

	/**
	 * @param CaptionAsset $captionAsset
	 * @param vLocalFileResource $contentResource
	 */
	protected function attachLocalFileResource(CaptionAsset $captionAsset, vLocalFileResource $contentResource)
	{
		if ($contentResource->getIsReady())
			return $this->attachFile($captionAsset, $contentResource->getLocalFilePath(), $contentResource->getKeepOriginalFile());

		$captionAsset->setStatus(asset::ASSET_STATUS_IMPORTING);
		$captionAsset->save();

		$contentResource->attachCreatedObject($captionAsset);
	}

	/**
	 * @param CaptionAsset $captionAsset
	 * @param FileSyncKey $srcSyncKey
	 */
	protected function attachFileSync(CaptionAsset $captionAsset, FileSyncKey $srcSyncKey)
	{
		$captionAsset->incrementVersion();
		$captionAsset->save();

		$newSyncKey = $captionAsset->getSyncKey(CaptionAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		vFileSyncUtils::createSyncFileLinkForKey($newSyncKey, $srcSyncKey);
		
		$fileSync = vFileSyncUtils::getLocalFileSyncForKey($newSyncKey, false);
		list($width, $height, $type, $attr) = vImageUtils::getImageSize($fileSync);

		$captionAsset->setWidth($width);
		$captionAsset->setHeight($height);
		$captionAsset->setSize($fileSync->getFileSize());
		
		$captionAsset->setStatus(CaptionAsset::ASSET_STATUS_READY);
		$captionAsset->save();
	}

	/**
	 * @param CaptionAsset $captionAsset
	 * @param vFileSyncResource $contentResource
	 */
	protected function attachFileSyncResource(CaptionAsset $captionAsset, vFileSyncResource $contentResource)
	{
		$syncable = vFileSyncObjectManager::retrieveObject($contentResource->getFileSyncObjectType(), $contentResource->getObjectId());
		$srcSyncKey = $syncable->getSyncKey($contentResource->getObjectSubType(), $contentResource->getVersion());

		return $this->attachFileSync($captionAsset, $srcSyncKey);
	}

	/**
	 * @param CaptionAsset $captionAsset
	 * @param IRemoteStorageResource $contentResource
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 */
	protected function attachRemoteStorageResource(CaptionAsset $captionAsset, IRemoteStorageResource $contentResource)
	{
		$resources = $contentResource->getResources();

		$captionAsset->setFileExt($contentResource->getFileExt());
		$captionAsset->incrementVersion();
		$captionAsset->setStatus(CaptionAsset::ASSET_STATUS_READY);
		$captionAsset->save();

		$syncKey = $captionAsset->getSyncKey(CaptionAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		foreach ($resources as $currentResource)
		{
			$storageProfile = StorageProfilePeer::retrieveByPK($currentResource->getStorageProfileId());
			$fileSync = vFileSyncUtils::createReadyExternalSyncFileForKey($syncKey, $currentResource->getUrl(), $storageProfile);
		}
	}

	/**
	 * @param CaptionAsset $captionAsset
	 * @param vContentResource $contentResource
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 */
	protected function attachContentResource(CaptionAsset $captionAsset, vContentResource $contentResource)
	{
    	switch($contentResource->getType())
    	{
			case 'vUrlResource':
				return $this->attachUrlResource($captionAsset, $contentResource);
				
			case 'vLocalFileResource':
				return $this->attachLocalFileResource($captionAsset, $contentResource);
				
			case 'vFileSyncResource':
				return $this->attachFileSyncResource($captionAsset, $contentResource);
				
			case 'vRemoteStorageResource':
			case 'vRemoteStorageResources':
				return $this->attachRemoteStorageResource($captionAsset, $contentResource);
				
			default:
				$msg = "Resource of type [" . get_class($contentResource) . "] is not supported";
				VidiunLog::err($msg);
				
				if($captionAsset->getStatus() == CaptionAsset::ASSET_STATUS_QUEUED || $captionAsset->getStatus() == CaptionAsset::ASSET_STATUS_NOT_APPLICABLE)
				{
					$captionAsset->setDescription($msg);
					$captionAsset->setStatus(asset::ASSET_STATUS_ERROR);
					$captionAsset->save();
				}
				
				throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($contentResource));
    	}
    }
    
    
	/**
	 * Serves caption by entry id and thumnail params id
	 *  
	 * @action serveByEntryId
	 * @param string $entryId
	 * @param int $captionParamId if not set, default caption will be used.
	 * @return file
	 * 
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_PARAMS_ID_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function serveByEntryIdAction($entryId, $captionParamId = null)
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

		$securyEntryHelper = new VSecureEntryHelper($entry, vCurrentContext::$vs, null, ContextType::DOWNLOAD);
		$securyEntryHelper->validateForDownload();
		
		$captionAsset = null;
		if(!$captionParamId)
		{
			$captionAssets = assetPeer::retrieveByEntryId($entryId, array(CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION)));
			foreach($captionAssets as $checkCaptionAsset)
			{
				if($checkCaptionAsset->getDefault())
				{
					$captionAsset = $checkCaptionAsset;
					break;
				}
			}
		}
		else
		{
			$captionAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $captionParamId);
		}
		
		if(!$captionAsset || !($captionAsset instanceof CaptionAsset))
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_PARAMS_ID_NOT_FOUND, $captionParamId);
		
		$fileName = $captionAsset->getId() . '.' . $captionAsset->getFileExt();
		
		return $this->serveAsset($captionAsset, $fileName);
	}
	
	/**
	 * Get download URL for the asset
	 * 
	 * @action getUrl
	 * @param string $id
	 * @param int $storageId
	 * @return string
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_IS_NOT_READY
	 */
	public function getUrlAction($id, $storageId = null)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof CaptionAsset))
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $id);

		$this->validateEntryEntitlement($assetDb->getEntryId(), $id);
		
		if ($assetDb->getStatus() != asset::ASSET_STATUS_READY)
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_IS_NOT_READY);

		$entryDb = $assetDb->getentry();
		if(is_null($entryDb))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $assetDb->getEntryId());
		
		if($storageId)
			return $assetDb->getExternalUrl($storageId);
			
		return $assetDb->getDownloadUrl(true);
	}
	
	/**
	 * Get remote storage existing paths for the asset
	 * 
	 * @action getRemotePaths
	 * @param string $id
	 * @return VidiunRemotePathListResponse
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_IS_NOT_READY
	 */
	public function getRemotePathsAction($id)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof CaptionAsset))
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $id);

		if ($assetDb->getStatus() != asset::ASSET_STATUS_READY)
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_IS_NOT_READY);

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
	 * @param string $captionAssetId
	 * @throws VidiunAPIException
	 * @return CaptionAsset
	 */
	protected function validateForDownload($captionAssetId)
	{
		$captionAsset = null;
		if (!vCurrentContext::$vs)
		{
			$captionAsset = vCurrentContext::initPartnerByAssetId($captionAssetId);
				
			if (!$captionAsset || $captionAsset->getStatus() == asset::ASSET_STATUS_DELETED)
				throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $captionAssetId);
		
			// enforce entitlement
			$this->setPartnerFilters(vCurrentContext::getCurrentPartnerId());
			vEntitlementUtils::initEntitlementEnforcement();
		}
		else
		{
			$captionAsset = assetPeer::retrieveById($captionAssetId);
		}
		
		if (!$captionAsset || !($captionAsset instanceof CaptionAsset))
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $captionAssetId);

		if (vCurrentContext::$vs_object && 
			vCurrentContext::$vs_object->verifyPrivileges(CaptionPlugin::VS_PRIVILEGE_CAPTION, $captionAsset->getEntryId()))
			return $captionAsset;
		
		$entry = entryPeer::retrieveByPK($captionAsset->getEntryId());
		if(!$entry)
		{
			//we will throw caption asset not found, as the user is not entitled, and should not know that the entry exists.
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $captionAssetId);
		}
		
		$securyEntryHelper = new KSecureEntryHelper($entry, kCurrentContext::$ks, null, ContextType::DOWNLOAD, array(), $captionAsset);
		$securyEntryHelper->validateForDownload();
		
		return $captionAsset;
	}

	/**
	 * Serves caption by its id
	 *  
	 * @action serve
	 * @param string $captionAssetId
	 * @return file
	 * @vsOptional
	 *  
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 */
	public function serveAction($captionAssetId)
	{
		$captionAsset = $this->validateForDownload($captionAssetId);

		$ext = $captionAsset->getFileExt();
		if(is_null($ext))
			$ext = 'txt';
			
		$fileName = $captionAsset->getEntryId()."_" . $captionAsset->getId() . ".$ext";
		
		return $this->serveAsset($captionAsset, $fileName);
	}	
	
	/**
	 * Serves caption by its id converting it to segmented WebVTT
	 *
	 * @action serveWebVTT
	 * @param string $captionAssetId
	 * @param int $segmentDuration
	 * @param int $segmentIndex
	 * @param int $localTimestamp
	 * @return file
	 * @vsOptional
	 *
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 */
	public function serveWebVTTAction($captionAssetId, $segmentDuration = 30, $segmentIndex = null, $localTimestamp = 10000)
	{
		$captionAsset = $this->validateForDownload($captionAssetId);

		if (!$segmentIndex)
		{
			entryPeer::setUseCriteriaFilter(false);
			$entry = entryPeer::retrieveByPK($captionAsset->getEntryId());
			if (!$entry)
				throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ENTRY_ID_NOT_FOUND, $captionAsset->getEntryId());
			entryPeer::setUseCriteriaFilter(true);

			return new vRendererString(vWebVTTGenerator::buildWebVTTM3U8File($segmentDuration, (int)$entry->getDuration()), 'application/x-mpegurl');
		}
		$syncKey = $captionAsset->getSyncKey(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$content = vFileSyncUtils::file_get_contents($syncKey, true, false, self::MAX_SERVE_WEBVTT_FILE_SIZE);
		if (!$content)
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_FILE_NOT_FOUND, $captionAssetId);

		$content = str_replace(
			array(
				vCaptionsContentManager::WINDOWS_LINE_ENDING,
				vCaptionsContentManager::MAC_LINE_ENDING,
			),
			vCaptionsContentManager::UNIX_LINE_ENDING,
			$content
		);

		$captionsContentManager = vCaptionsContentManager::getCoreContentManager($captionAsset->getContainerFormat());
		if (!$captionsContentManager)
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_INVALID_FORMAT, $captionAssetId);

		if ($captionAsset->getContainerFormat() == CaptionType::WEBVTT)
			return new vRendererString(vWebVTTGenerator::getSegmentFromWebVTT($captionsContentManager, $content, $segmentIndex, $segmentDuration, $localTimestamp), 'text/vtt');
		else
		{
			$parsedCaption = $captionsContentManager->parse($content);
			if (!$parsedCaption)
				throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_PARSING_FAILED, $captionAssetId);
			return new vRendererString(vWebVTTGenerator::buildWebVTTSegment($parsedCaption, $segmentIndex, $segmentDuration, $localTimestamp), 'text/vtt');
		}
	}
	
	/**
	 * Markss the caption as default and removes that mark from all other caption assets of the entry.
	 *  
	 * @action setAsDefault
	 * @param string $captionAssetId
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 * @validateUser asset::entry captionAssetId edit
	 */
	public function setAsDefaultAction($captionAssetId)
	{
		$captionAsset = assetPeer::retrieveById($captionAssetId);
		if (!$captionAsset || !($captionAsset instanceof CaptionAsset))
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $captionAssetId);
		
		$entry = $captionAsset->getentry();
    	if(!$entry || !in_array($entry->getType(), $this->getEnabledMediaTypes()) || !in_array($entry->getMediaType(), array(VidiunMediaType::VIDEO, VidiunMediaType::AUDIO)))
    		throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $captionAsset->getEntryId());
		
		
		$entryVuserId = $entry->getVuserId();
		$thisVuserId = $this->getVuser()->getId();
		$isNotAdmin = !vCurrentContext::$vs_object->isAdmin();
		
		if(!$entry || ($isNotAdmin && !is_null($entryVuserId) && $entryVuserId != $thisVuserId))  
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $captionAsset->getEntryId());
			
		$entryCaptionAssets = assetPeer::retrieveByEntryId($captionAsset->getEntryId(), array(CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION)));
		foreach($entryCaptionAssets as $entryCaptionAsset)
		{
			if($entryCaptionAsset->getId() == $captionAsset->getId())
				$entryCaptionAsset->setDefault(true);
			else
				$entryCaptionAsset->setDefault(false);
				
			$entryCaptionAsset->save();
		}
	}

	/**
	 * @action get
	 * @param string $captionAssetId
	 * @return VidiunCaptionAsset
	 * 
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 */
	public function getAction($captionAssetId)
	{
		$captionAssetsDb = assetPeer::retrieveById($captionAssetId);
		if (!$captionAssetsDb || !($captionAssetsDb instanceof CaptionAsset))
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $captionAssetId);
		
		$captionAssets = new VidiunCaptionAsset();
		$captionAssets->fromObject($captionAssetsDb, $this->getResponseProfile());
		return $captionAssets;
	}
	
	/**
	 * List caption Assets by filter and pager
	 * 
	 * @action list
	 * @param VidiunAssetFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunCaptionAssetListResponse
	 */
	function listAction(VidiunAssetFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
		{
			$filter = new VidiunCaptionAssetFilter();
		}
		elseif(! $filter instanceof VidiunCaptionAssetFilter)
		{
			$filter = $filter->cast('VidiunCaptionAssetFilter');
		}
			
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}

		$types = VidiunPluginManager::getExtendedTypes(assetPeer::OM_CLASS, CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION));
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $types);
	}
	
	/**
	 * @action delete
	 * @param string $captionAssetId
	 * 
	 * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
	 * @validateUser asset::entry captionAssetId edit
	 */
	public function deleteAction($captionAssetId)
	{
		$captionAssetDb = assetPeer::retrieveById($captionAssetId);
		if (!$captionAssetDb || !($captionAssetDb instanceof CaptionAsset))
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $captionAssetId);
	
// 		if($captionAssetDb->getDefault())
// 			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_IS_DEFAULT, $captionAssetId);
		
		$dbEntry = $captionAssetDb->getentry();
    	if(!$dbEntry || !in_array($dbEntry->getType(), $this->getEnabledMediaTypes()) || !in_array($dbEntry->getMediaType(), array(VidiunMediaType::VIDEO, VidiunMediaType::AUDIO)))
    		throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $captionAssetDb->getEntryId());
		
		
		$captionAssetDb->setStatus(CaptionAsset::ASSET_STATUS_DELETED);
		$captionAssetDb->setDeletedAt(time());
		$captionAssetDb->save();
	}
}

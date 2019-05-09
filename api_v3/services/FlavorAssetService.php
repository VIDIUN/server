<?php

/**
 * Retrieve information and invoke actions on Flavor Asset
 *
 * @service flavorAsset
 * @package api
 * @subpackage services
 */
class FlavorAssetService extends VidiunAssetService
{
	protected function vidiunNetworkAllowed($actionName)
	{
		if(
			$actionName == 'add' ||
			$actionName == 'get' ||
			$actionName == 'list' ||
			$actionName == 'getByEntryId' ||
			$actionName == 'getUrl' ||
			$actionName == 'getDownloadUrl' ||
			$actionName == 'getWebPlayableByEntryId' ||
			$actionName == 'getFlavorAssetsWithParams' ||
			$actionName == 'convert' ||
			$actionName == 'reconvert' ||
			$actionName == 'setContent' || 
			$actionName == 'serveAdStitchCmd'
			)
		{
			$this->partnerGroup .= ',0';
			return true;
		}
			
		return parent::vidiunNetworkAllowed($actionName);
	}
	
    /**
     * Add flavor asset
     *
     * @action add
     * @param string $entryId
     * @param VidiunFlavorAsset $flavorAsset
     * @return VidiunFlavorAsset
     * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
     * @throws VidiunErrors::FLAVOR_ASSET_ALREADY_EXISTS
     * @validateUser entry entryId edit
     */
    function addAction($entryId, VidiunFlavorAsset $flavorAsset)
    {
    	$dbEntry = entryPeer::retrieveByPK($entryId);
    	if(!$dbEntry || $dbEntry->getType() != VidiunEntryType::MEDIA_CLIP || !in_array($dbEntry->getMediaType(), array(VidiunMediaType::VIDEO, VidiunMediaType::AUDIO)))
    		throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
    	if(!is_null($flavorAsset->flavorParamsId))
    	{
    		$dbFlavorAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $flavorAsset->flavorParamsId);
    		if($dbFlavorAsset)
    			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ALREADY_EXISTS, $dbFlavorAsset->getId(), $flavorAsset->flavorParamsId);
    	}
    	
    	if(!is_null($flavorAsset->flavorParamsId))
    	{
    		$flavorParams = assetParamsPeer::retrieveByPK($flavorAsset->flavorParamsId);
    	}
    	
    	$type = null;
    	if($flavorParams)
    		$type = $flavorParams->getType();
    		
    	$dbFlavorAsset = flavorAsset::getInstance($type);
    	$dbFlavorAsset = $flavorAsset->toInsertableObject($dbFlavorAsset);
    	/* @var $dbFlavorAsset flavorAsset */
    	
    	if($flavorParams && $flavorParams->hasTag(flavorParams::TAG_SOURCE))
    	{
     		$dbFlavorAsset->setIsOriginal(true);
    	}
    	
		$dbFlavorAsset->setEntryId($entryId);
		$dbFlavorAsset->setPartnerId($dbEntry->getPartnerId());
		$dbFlavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_QUEUED);
		$dbFlavorAsset->save();
    	
		$flavorAsset = VidiunFlavorAsset::getInstance($dbFlavorAsset, $this->getResponseProfile());
		return $flavorAsset;
    }
    
    /**
     * Update flavor asset
     *
     * @action update
     * @param string $id
     * @param VidiunFlavorAsset $flavorAsset
     * @return VidiunFlavorAsset
     * @throws VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND
     * @validateUser asset::entry id edit
     */
    function updateAction($id, VidiunFlavorAsset $flavorAsset)
    {
   		$dbFlavorAsset = assetPeer::retrieveById($id);
   		if (!$dbFlavorAsset || !($dbFlavorAsset instanceof flavorAsset))
   			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbFlavorAsset->getentry();
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbFlavorAsset->getEntryId());
			
		
		
    	$dbFlavorAsset = $flavorAsset->toUpdatableObject($dbFlavorAsset);
   		$dbFlavorAsset->save();
		
		$flavorAsset = VidiunFlavorAsset::getInstance($dbFlavorAsset, $this->getResponseProfile());
		return $flavorAsset;
    }
    
    /**
     * Update content of flavor asset
     *
     * @action setContent
     * @param string $id
     * @param VidiunContentResource $contentResource
     * @return VidiunFlavorAsset
     * @throws VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED 
	 * @validateUser asset::entry id edit
     */
    function setContentAction($id, VidiunContentResource $contentResource)
    {
   		$dbFlavorAsset = assetPeer::retrieveById($id);
   		if (!$dbFlavorAsset || !($dbFlavorAsset instanceof flavorAsset))
   			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbFlavorAsset->getentry();
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbFlavorAsset->getEntryId());
			
		
		
		$contentResource->validateEntry($dbFlavorAsset->getentry());
		$contentResource->validateAsset($dbFlavorAsset);
		$vContentResource = $contentResource->toObject();
    	$this->attachContentResource($dbFlavorAsset, $vContentResource);
		$contentResource->entryHandled($dbFlavorAsset->getentry());
		vEventsManager::raiseEvent(new vObjectDataChangedEvent($dbFlavorAsset));
		
    	$newStatuses = array(
    	    asset::ASSET_STATUS_EXPORTING,
    		flavorAsset::FLAVOR_ASSET_STATUS_READY,
    		flavorAsset::FLAVOR_ASSET_STATUS_QUEUED,
    		flavorAsset::FLAVOR_ASSET_STATUS_TEMP,
    	);
    	
    	if(in_array($dbFlavorAsset->getStatus(), $newStatuses))
   			vEventsManager::raiseEvent(new vObjectAddedEvent($dbFlavorAsset));
   		
		$flavorAsset = VidiunFlavorAsset::getInstance($dbFlavorAsset, $this->getResponseProfile());
		return $flavorAsset;
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param string $fullPath
	 * @param bool $copyOnly
	 */
	protected function attachFile(flavorAsset $flavorAsset, $fullPath, $copyOnly = false)
	{
		$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
		$flavorAsset->setFileExt($ext);
		$flavorAsset->setSize(vFile::fileSize($fullPath));
		$flavorAsset->incrementVersion();
		$flavorAsset->save();
		
		$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		
		try 
		{
			vFileSyncUtils::moveFromFile($fullPath, $syncKey, true, $copyOnly);
		}
		catch (Exception $e) 
		{
        	if($flavorAsset->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_QUEUED || $flavorAsset->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_NOT_APPLICABLE)
        	{
				$flavorAsset->setDescription($e->getMessage());
				$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
				$flavorAsset->save();
        	}												
			throw $e;
		}
		
        if(!$flavorAsset->isLocalReadyStatus())
			$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_QUEUED);
			
		$flavorAsset->save();
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param string $url
	 * @param vImportJobData $importJobData
	 */
	protected function attachUrl(flavorAsset $flavorAsset, $url, vImportJobData $importJobData = null)
	{
		$flavorAsset->save();
		
		vJobsManager::addImportJob(null, $flavorAsset->getEntryId(), $this->getPartnerId(), $url, $flavorAsset, null, $importJobData);
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param vUrlResource $contentResource
	 */
	protected function attachUrlResource(flavorAsset $flavorAsset, vUrlResource $contentResource)
	{
    	$this->attachUrl($flavorAsset, $contentResource->getUrl(), $contentResource->getImportJobData());
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param VidiunSearchResultsResource $contentResource
	 */
	protected function VidiunSearchResultsResource(flavorAsset $flavorAsset, VidiunSearchResultsResource $contentResource)
	{
    	$contentResource->validatePropertyNotNull('result');
     	$contentResource->result->validatePropertyNotNull("searchSource");
     	
		if ($contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_VIDIUN ||
			$contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_VIDIUN_PARTNER ||
			$contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_VIDIUN_PARTNER_VSHOW ||
			$contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_VIDIUN_VSHOW ||
			$contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_VIDIUN_USER_CLIPS)
		{
			$srcFlavorAsset = assetPeer::retrieveOriginalByEntryId($contentResource->result->id); 
			$this->attachAsset($flavorAsset, $srcFlavorAsset);
		}
		else
		{
			$this->attachUrl($flavorAsset, $contentResource->result->url);
		}
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param vLocalFileResource $contentResource
	 */
	protected function attachLocalFileResource(flavorAsset $flavorAsset, vLocalFileResource $contentResource)
	{
		if($contentResource->getIsReady())
			return $this->attachFile($flavorAsset, $contentResource->getLocalFilePath(), $contentResource->getKeepOriginalFile());
			
//		I'm not sure about it...
//				
//		$lowerStatuses = array(
//			entryStatus::ERROR_CONVERTING,
//			entryStatus::ERROR_IMPORTING,
//			entryStatus::PENDING,
//			entryStatus::NO_CONTENT,
//		);
//		
//		$entry = $flavorAsset->getentry();
//		if(in_array($entry->getStatus(), $lowerStatuses))
//		{
//			$entry->setStatus(entryStatus::IMPORT);
//			$entry->save();
//		}
    		
		$flavorAsset->setStatus(asset::FLAVOR_ASSET_STATUS_IMPORTING);
		$flavorAsset->save();
		
		$contentResource->attachCreatedObject($flavorAsset);
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param FileSyncKey $srcSyncKey
	 */
	protected function attachFileSync(flavorAsset $flavorAsset, FileSyncKey $srcSyncKey)
	{
		$flavorAsset->incrementVersion();
		$flavorAsset->save();
		
        $newSyncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
        vFileSyncUtils::createSyncFileLinkForKey($newSyncKey, $srcSyncKey);
                
        $fileSync = vFileSyncUtils::getLocalFileSyncForKey($newSyncKey, false);
        $fileSync = vFileSyncUtils::resolve($fileSync);
        
        if(!$flavorAsset->isLocalReadyStatus())
			$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_QUEUED);
		
		$flavorAsset->setSize($fileSync->getFileSize());
		$flavorAsset->save();
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param vFileSyncResource $contentResource
	 */
	protected function attachFileSyncResource(flavorAsset $flavorAsset, vFileSyncResource $contentResource)
	{
    	$syncable = vFileSyncObjectManager::retrieveObject($contentResource->getFileSyncObjectType(), $contentResource->getObjectId());
    	$srcSyncKey = $syncable->getSyncKey($contentResource->getObjectSubType(), $contentResource->getVersion());
    	
        return $this->attachFileSync($flavorAsset, $srcSyncKey);
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param IRemoteStorageResource $contentResource
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 */
	protected function attachRemoteStorageResource(flavorAsset $flavorAsset, IRemoteStorageResource $contentResource)
	{
		$resources = $contentResource->getResources();
		$flavorAsset->setFileExt($contentResource->getFileExt());
		$flavorAsset->incrementVersion();
		$flavorAsset->save();
		
        $syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		foreach($resources as $currentResource)
		{
			$storageProfile = StorageProfilePeer::retrieveByPK($currentResource->getStorageProfileId());
			$fileSync = vFileSyncUtils::createReadyExternalSyncFileForKey($syncKey, $currentResource->getUrl(), $storageProfile);
		}
		
		if($flavorAsset->getIsOriginal())
			$flavorAsset->setStatus(asset::FLAVOR_ASSET_STATUS_QUEUED);
		else
		    $flavorAsset->setStatusLocalReady();
			
		$flavorAsset->save();
		
		vBusinessPostConvertDL::handleConvertFinished(null, $flavorAsset);
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param vContentResource $contentResource
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 */
	protected function attachContentResource(flavorAsset $flavorAsset, vContentResource $contentResource)
	{
    	switch($contentResource->getType())
    	{
			case 'vUrlResource':
				return $this->attachUrlResource($flavorAsset, $contentResource);
				
			case 'vLocalFileResource':
				return $this->attachLocalFileResource($flavorAsset, $contentResource);
				
			case 'vFileSyncResource':
				return $this->attachFileSyncResource($flavorAsset, $contentResource);
				
			case 'vRemoteStorageResource':
			case 'vRemoteStorageResources':
				return $this->attachRemoteStorageResource($flavorAsset, $contentResource);
				
			default:
				$msg = "Resource of type [" . get_class($contentResource) . "] is not supported";
				VidiunLog::err($msg);
				
				if($flavorAsset->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_QUEUED || $flavorAsset->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_NOT_APPLICABLE)
				{
					$flavorAsset->setDescription($msg);
					$flavorAsset->setStatus(asset::FLAVOR_ASSET_STATUS_ERROR);
					$flavorAsset->save();
				}
				
				throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($contentResource));
    	}
    }
    
	protected function globalPartnerAllowed($actionName)
	{
		if ($actionName === 'getFlavorAssetsWithParams') {
			return true;
		}
		return parent::globalPartnerAllowed($actionName);
	}
	
	/**
	 * Get Flavor Asset by ID
	 * 
	 * @action get
	 * @param string $id
	 * @return VidiunFlavorAsset
	 */
	public function getAction($id)
	{
		$flavorAssetDb = assetPeer::retrieveById($id);
		if (!$flavorAssetDb || !($flavorAssetDb instanceof flavorAsset))
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
			
		$flavorAsset = VidiunFlavorAsset::getInstance($flavorAssetDb, $this->getResponseProfile());
		return $flavorAsset;
	}
	
	/**
	 * Get Flavor Assets for Entry
	 * 
	 * @action getByEntryId
	 * @param string $entryId
	 * @return VidiunFlavorAssetArray
	 * @deprecated Use thumbAsset.list instead
	 */
	public function getByEntryIdAction($entryId)
	{
		// entry could be "display_in_search = 2" - in that case we want to pull it although VN is off in services.ct for this action
		$c = VidiunCriteria::create(entryPeer::OM_CLASS);
		$c->addAnd(entryPeer::ID, $entryId);
		$criterionPartnerOrKn = $c->getNewCriterion(entryPeer::PARTNER_ID, $this->getPartnerId());
		$criterionPartnerOrKn->addOr($c->getNewCriterion(entryPeer::DISPLAY_IN_SEARCH, mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK));
		$c->addAnd($criterionPartnerOrKn);
		// there could only be one entry because the query is by primary key.
		// so using doSelectOne is safe.
		$dbEntry = entryPeer::doSelectOne($c);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
					
		$flavorAssetsDb = assetPeer::retrieveFlavorsByEntryId($entryId);
		$flavorAssets = VidiunFlavorAssetArray::fromDbArray($flavorAssetsDb, $this->getResponseProfile());
		return $flavorAssets;
	}
	
	/**
	 * List Flavor Assets by filter and pager
	 * 
	 * @action list
	 * @param VidiunAssetFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunFlavorAssetListResponse
	 */
	function listAction(VidiunAssetFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
		{
			$filter = new VidiunFlavorAssetFilter();
		}
		elseif(! $filter instanceof VidiunFlavorAssetFilter)
		{
			$filter = $filter->cast('VidiunFlavorAssetFilter');
		}
			
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}
		
		$types = assetPeer::retrieveAllFlavorsTypes();
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $types);
	}
	
	/**
	 * Get web playable Flavor Assets for Entry
	 * 
	 * @action getWebPlayableByEntryId
	 * @param string $entryId
	 * @return VidiunFlavorAssetArray
	 * 
	 * @deprecated use baseEntry.getContextData instead
	 */
	public function getWebPlayableByEntryIdAction($entryId)
	{
		// entry could be "display_in_search = 2" - in that case we want to pull it although VN is off in services.ct for this action
		$c = VidiunCriteria::create(entryPeer::OM_CLASS);
		$c->addAnd(entryPeer::ID, $entryId);
		$criterionPartnerOrKn = $c->getNewCriterion(entryPeer::PARTNER_ID, $this->getPartnerId());
		$criterionPartnerOrKn->addOr($c->getNewCriterion(entryPeer::DISPLAY_IN_SEARCH, mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK));
		$c->addAnd($criterionPartnerOrKn);
		// there could only be one entry because the query is by primary key.
		// so using doSelectOne is safe.
		$dbEntry = entryPeer::doSelectOne($c);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		$flavorAssetsDb = assetPeer::retrieveReadyWebByEntryId($entryId);
		if (count($flavorAssetsDb) == 0)
			throw new VidiunAPIException(VidiunErrors::NO_FLAVORS_FOUND);
			
		$flavorAssets = VidiunFlavorAssetArray::fromDbArray($flavorAssetsDb, $this->getResponseProfile());
		
		return $flavorAssets;
	}
	
	/**
	 * Add and convert new Flavor Asset for Entry with specific Flavor Params
	 * 
	 * @action convert
	 * @param string $entryId
	 * @param int $flavorParamsId
	 * @param int $priority
	 * @validateUser entry entryId edit
	 */
	public function convertAction($entryId, $flavorParamsId, $priority = 0)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		
		
		$flavorParamsDb = assetParamsPeer::retrieveByPK($flavorParamsId);
		assetParamsPeer::setUseCriteriaFilter(false);
		if (!$flavorParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $flavorParamsId);
				
		$validStatuses = array(
			entryStatus::ERROR_CONVERTING,
			entryStatus::PRECONVERT,
			entryStatus::READY,
		);
		
		if (!in_array($dbEntry->getStatus(), $validStatuses))
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_STATUS);
			
		$conversionProfile = $dbEntry->getconversionProfile2();
		if(!$conversionProfile)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $dbEntry->getConversionProfileId());
		
		$originalFlavorAsset = assetPeer::retrieveOriginalByEntryId($entryId);
		if (is_null($originalFlavorAsset) || !$originalFlavorAsset->isLocalReadyStatus())
			throw new VidiunAPIException(VidiunErrors::ORIGINAL_FLAVOR_ASSET_IS_MISSING);

		$srcSyncKey = $originalFlavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		// if the file sync isn't local (wasn't synced yet) proxy request to other datacenter
		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($srcSyncKey, true, false);
		/* @var $fileSync FileSync */
		if(!$fileSync)
		{
			throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);
		}
		
		if(!$local && $fileSync->getFileType() != FileSync::FILE_SYNC_FILE_TYPE_URL)
		{
			vFileUtils::dumpApiRequest(vDataCenterMgr::getRemoteDcExternalUrl($fileSync));
		}
		$err = "";
		
		$dynamicFlavorAttributes = $dbEntry->getDynamicFlavorAttributesForAssetParams($flavorParamsDb->getId());
		
		vBusinessPreConvertDL::decideAddEntryFlavor(null, $dbEntry->getId(), $flavorParamsId, $err, null, $dynamicFlavorAttributes, $priority);
	}
	
	/**
	 * Reconvert Flavor Asset by ID
	 * 
	 * @action reconvert
	 * @param string $id Flavor Asset ID
	 * @validateUser asset::entry id edit
	 */
	public function reconvertAction($id)
	{
		$flavorAssetDb = assetPeer::retrieveById($id);
		if (!$flavorAssetDb || !($flavorAssetDb instanceof flavorAsset))
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
			
		if ($flavorAssetDb->getIsOriginal())
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_RECONVERT_ORIGINAL);
			
		$flavorParamsId = $flavorAssetDb->getFlavorParamsId();
		$entryId = $flavorAssetDb->getEntryId();
		
		return $this->convertAction($entryId, $flavorParamsId);
	} 
	
	/**
	 * Delete Flavor Asset by ID
	 * 
	 * @action delete
	 * @param string $id
	 * @validateUser asset::entry id edit
	 */
	public function deleteAction($id)
	{
		$flavorAssetDb = assetPeer::retrieveById($id);
		if (!$flavorAssetDb || !($flavorAssetDb instanceof flavorAsset))
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
			
		$entry = $flavorAssetDb->getEntry();
		if (!$entry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $flavorAssetDb->getEntryId());
			
		$flavorAssetDb->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_DELETED);
		$flavorAssetDb->setDeletedAt(time());
		$flavorAssetDb->save();		
	}
	
	/**
	 * Get download URL for the asset
	 * 
	 * @action getUrl
	 * @param string $id
	 * @param int $storageId
	 * @param bool $forceProxy
	 * @param VidiunFlavorAssetUrlOptions $options
	 * @return string
	 * @throws VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::FLAVOR_ASSET_IS_NOT_READY
	 */
	public function getUrlAction($id, $storageId = null, $forceProxy = false, VidiunFlavorAssetUrlOptions $options = null)
	{
		if (!$options)
		{
			$options = new VidiunFlavorAssetUrlOptions();
		}
		
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof flavorAsset))
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);

		$this->validateEntryEntitlement($assetDb->getEntryId(), $id);
		
		if (!$assetDb->isLocalReadyStatus())
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_IS_NOT_READY);
	
		if($storageId)
			return $assetDb->getExternalUrl($storageId, $options->fileName);
		
		// Validate for download
		$entryDb = entryPeer::retrieveByPK($assetDb->getEntryId());
		if(is_null($entryDb))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $assetDb->getEntryId());
		
		$shouldServeFlavor = false;
		if($entryDb->getType() == entryType::MEDIA_CLIP &&!in_array($assetDb->getPartnerId(),vConf::get('legacy_get_url_partners', 'local', array())))
		{
			$shouldServeFlavor = true;
			$preview = null;
		}
		else
			$previewFileSize = null;
		$vsObj = $this->getVs();
		$vs = ($vsObj) ? $vsObj->getOriginalString() : null;

		$referrer = null;
		if($options && $options->referrer)
			$referrer = $options->referrer;

		$secureEntryHelper = new VSecureEntryHelper($entryDb, $vs, $referrer, ContextType::DOWNLOAD);

		if ($secureEntryHelper->shouldPreview())
		{ 
			if ($shouldServeFlavor)
				$preview = $secureEntryHelper->getPreviewLength() * 1000;
			else
				$previewFileSize = $assetDb->estimateFileSize($entryDb, $secureEntryHelper->getPreviewLength());
		}
		else
			$secureEntryHelper->validateForDownload();
		
		if (!$secureEntryHelper->isAssetAllowed($assetDb))
			throw new VidiunAPIException(VidiunErrors::ASSET_NOT_ALLOWED, $id);
 
		if ($shouldServeFlavor)
			return $assetDb->getServeFlavorUrl($preview, $options->fileName);
		
		return $assetDb->getDownloadUrl(true, $forceProxy,$previewFileSize, $options->fileName);
	}
	
	/**
	 * Get remote storage existing paths for the asset
	 * 
	 * @action getRemotePaths
	 * @param string $id
	 * @return VidiunRemotePathListResponse
	 * @throws VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::FLAVOR_ASSET_IS_NOT_READY
	 */
	public function getRemotePathsAction($id)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof flavorAsset))
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);

		if ($assetDb->getStatus() != asset::FLAVOR_ASSET_STATUS_READY)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_IS_NOT_READY);

		$c = new Criteria();
		$c->add(FileSyncPeer::OBJECT_TYPE, FileSyncObjectType::FLAVOR_ASSET);
		$c->add(FileSyncPeer::OBJECT_SUB_TYPE, asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
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
	 * Get download URL for the Flavor Asset
	 * 
	 * @action getDownloadUrl
	 * @param string $id
	 * @param bool $useCdn
	 * @return string
	 * @deprecated use getUrl instead
	 */
	public function getDownloadUrlAction($id, $useCdn = false)
	{
		$flavorAssetDb = assetPeer::retrieveById($id);
		if (!$flavorAssetDb || !($flavorAssetDb instanceof flavorAsset))
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);

		$this->validateEntryEntitlement($flavorAssetDb->getEntryId(), $id);		
			
		if ($flavorAssetDb->getStatus() != flavorAsset::FLAVOR_ASSET_STATUS_READY)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_IS_NOT_READY);
		
		// Validate for download
		$entryDb = entryPeer::retrieveByPK($flavorAssetDb->getEntryId());
		if(is_null($entryDb))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $flavorAssetDb->getEntryId());
		
		$preview = null;
		$vsObj = $this->getVs();
		$vs = ($vsObj) ? $vsObj->getOriginalString() : null;
		$secureEntryHelper = new VSecureEntryHelper($entryDb, $vs, null, ContextType::DOWNLOAD);
		if ($secureEntryHelper->shouldPreview()) {
			$preview = $flavorAssetDb->estimateFileSize($entryDb, $secureEntryHelper->getPreviewLength());
		} else {
			$secureEntryHelper->validateForDownload();
		}
		
		return $flavorAssetDb->getDownloadUrl($useCdn, false, $preview);
	}
	
	/**
	 * Get Flavor Asset with the relevant Flavor Params (Flavor Params can exist without Flavor Asset & vice versa)
	 * 
	 * @action getFlavorAssetsWithParams
	 * @param string $entryId
	 * @return VidiunFlavorAssetWithParamsArray
	 */
	public function getFlavorAssetsWithParamsAction($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		// get all the flavor params of partner 0 and the current partner (note that partner 0 is defined as partner group in service.ct)
		$c = new Criteria();
		$flavorTypes = assetParamsPeer::retrieveAllFlavorParamsTypes();
		$c->add(assetParamsPeer::TYPE, $flavorTypes, Criteria::IN);
		$partnerIds = array($dbEntry->getPartnerId(), PartnerPeer::GLOBAL_PARTNER);
		$c->add(assetParamsPeer::PARTNER_ID, array_map('strval', $partnerIds), Criteria::IN);
		
		$flavorParamsDb = assetParamsPeer::doSelect($c);
		
		// get the flavor assets for this entry
		$c = new Criteria();

		$flavorTypes = assetPeer::retrieveAllFlavorsTypes();
		$c->add(assetPeer::TYPE, $flavorTypes, Criteria::IN);

		$c->add(assetPeer::ENTRY_ID, $entryId);
		$c->add(assetPeer::STATUS, array(flavorAsset::FLAVOR_ASSET_STATUS_DELETED, flavorAsset::FLAVOR_ASSET_STATUS_TEMP), Criteria::NOT_IN);
		$flavorAssetsDb = assetPeer::doSelect($c);
		
		// find what flavot params are required
		$requiredFlavorParams = array();
		foreach($flavorAssetsDb as $item)
			$requiredFlavorParams[$item->getFlavorParamsId()] = true;
		
		// now merge the results, first organize the flavor params in an array with the id as the key
		$flavorParamsArray = array();
		foreach($flavorParamsDb as $item)
		{
			$flavorParams = $item->getId();
			$flavorParamsArray[$flavorParams] = $item;
			
			if(isset($requiredFlavorParams[$flavorParams]))
				unset($requiredFlavorParams[$flavorParams]);
		}

		// adding missing required flavors params to the list
		if(count($requiredFlavorParams))
		{
			$flavorParamsDb = assetParamsPeer::retrieveByPKsNoFilter(array_keys($requiredFlavorParams));
			foreach($flavorParamsDb as $item)
				$flavorParamsArray[$item->getId()] = $item;
		}
		
		$usedFlavorParams = array();
		
		// loop over the flavor assets and add them, if it has flavor params add them too
		$flavorAssetWithParamsArray = new VidiunFlavorAssetWithParamsArray();
		foreach($flavorAssetsDb as $flavorAssetDb)
		{
			$flavorParamsId = $flavorAssetDb->getFlavorParamsId();
			$flavorAssetWithParams = new VidiunFlavorAssetWithParams();
			$flavorAssetWithParams->entryId = $entryId;
			$flavorAsset = VidiunFlavorAsset::getInstance($flavorAssetDb, $this->getResponseProfile());
			$flavorAssetWithParams->flavorAsset = $flavorAsset;
			if (isset($flavorParamsArray[$flavorParamsId]))
			{
				$flavorParamsDb = $flavorParamsArray[$flavorParamsId];
				$flavorParams = VidiunFlavorParamsFactory::getFlavorParamsInstance($flavorParamsDb->getType());
				$flavorParams->fromObject($flavorParamsDb, $this->getResponseProfile());
				$flavorAssetWithParams->flavorParams = $flavorParams;

				// we want to log which flavor params are in use, there could be more
				// than one flavor asset using same params
				$usedFlavorParams[$flavorParamsId] = $flavorParamsId;
			}
//			else if ($flavorAssetDb->getIsOriginal())
//			{
//				// create a dummy flavor params
//				$flavorParams = new VidiunFlavorParams();
//				$flavorParams->name = "Original source";
//				$flavorAssetWithParams->flavorParams = $flavorParams;
//			}
			
			$flavorAssetWithParamsArray[] = $flavorAssetWithParams;
		}
		
		// copy the remaining params
		foreach($flavorParamsArray as $flavorParamsId => $flavorParamsDb)
		{
			if(isset($usedFlavorParams[$flavorParamsId]))
			{
				// flavor params already exists for a flavor asset, not need
				// to list it one more time
				continue;
			}
			$flavorParams = VidiunFlavorParamsFactory::getFlavorParamsInstance($flavorParamsDb->getType());
			$flavorParams->fromObject($flavorParamsDb, $this->getResponseProfile());
			
			$flavorAssetWithParams = new VidiunFlavorAssetWithParams();
			$flavorAssetWithParams->entryId = $entryId;
			$flavorAssetWithParams->flavorParams = $flavorParams;
			$flavorAssetWithParamsArray[] = $flavorAssetWithParams;
		}
		
		return $flavorAssetWithParamsArray;
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
	
	/**
	 * Set a given flavor as the original flavor
	 * 
	 * @action setAsSource
	 * @param string $assetId
	 * @validateUser entry entryId edit
	 * @throws VidiunErrors::ASSET_ID_NOT_FOUND
	 */
	public function setAsSourceAction($assetId)
	{
		// Retrieve required
		$asset = assetPeer::retrieveById($assetId);
		if(is_null($asset)) 
			throw new VidiunAPIException(VidiunErrors::ASSET_ID_NOT_FOUND, $assetId);
		
		if($asset->getIsOriginal())
			return;
		
		// Retrieve original
		$originalAsset = assetPeer::retrieveOriginalByEntryId($asset->getEntryId());
		if(!is_null($originalAsset)) {
			// Set original to non-original
			$originalAsset->setIsOriginal(false);
			$originalAsset->save();
		}
		
		// Set required as original
		$asset->setIsOriginal(true);
		$asset->save();
	}

	/**
	 * delete all local file syncs for this asset
	 *
	 * @action deleteLocalContent
	 * @param string $assetId
	 * @validateUser asset::entry assetId edit
	 * @throws VidiunAPIException
	 */
	public function deleteLocalContentAction($assetId)
	{
		// Retrieve required
		$asset = assetPeer::retrieveById($assetId);
		if(is_null($asset))
			throw new VidiunAPIException(VidiunErrors::ASSET_ID_NOT_FOUND, $assetId);

		$srcSyncKey = $asset->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);

		$externalFileSyncs = vFileSyncUtils::getReadyExternalFileSyncForKey($srcSyncKey);
		if (!$externalFileSyncs)
			throw new VidiunAPIException(VidiunErrors::NO_EXTERNAL_CONTENT_EXISTS);

		$fileSyncs = vFileSyncUtils::getReadyInternalFileSyncsForKey($srcSyncKey);
		foreach ($fileSyncs as $fileSync){
			/* @var $fileSync FileSync*/
			$fileSync->setStatus(FileSync::FILE_SYNC_STATUS_DELETED);
			$fileSync->save();
		}
	}

	/**
	 * serve cmd line to transcode the ad
	 *
	 * @action serveAdStitchCmd
	 * @param string $assetId
	 * @param string $ffprobeJson
	 * @param string $duration
	 *
	 * @throws VidiunAPIException
	 * @return string command to transcode with
	 */
	public function serveAdStitchCmdAction($assetId, $ffprobeJson = null ,$duration = null)
	{
		$asset = assetPeer::retrieveById($assetId);
		if(is_null($asset))
			throw new VidiunAPIException(VidiunErrors::ASSET_ID_NOT_FOUND, $assetId);

		$flavorParamsId = $asset->getFlavorParamsId();

		$flavorParamsDb = assetParamsPeer::retrieveByPK($flavorParamsId);

		if (!$flavorParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $flavorParamsId);

		$flavorParamsOutputDb = assetParamsOutputPeer::retrieveByAssetId($assetId);

		if (!$flavorParamsOutputDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_OUTPUT_ID_NOT_FOUND, $assetId);

		try
		{
			$cmdLine = vBusinessConvertDL::generateAdStitchingCmdline($flavorParamsDb, $flavorParamsOutputDb, $ffprobeJson, $duration);
			if (empty($cmdLine))
				throw new VidiunAPIException(VidiunErrors::GENERATE_TRANSCODING_COMMAND_FAIL, $assetId, $ffprobeJson, 'Got null as response');
			return $cmdLine;
		} catch (vCoreException $e) {
			throw new VidiunAPIException(VidiunErrors::GENERATE_TRANSCODING_COMMAND_FAIL, $assetId, $ffprobeJson, $e->getMessage());
		}
	}



	/**
	 * Get volume map by entry id
	 *
	 * @action getVolumeMap
	 * @param string $flavorId Flavor id
	 * @return file
	 * @throws VidiunErrors::INVALID_FLAVOR_ASSET_ID
	 */
	function getVolumeMapAction($flavorId)
	{
		$flavorAsset = assetPeer::retrieveById($flavorId);
		if(!$flavorAsset)
			throw new VidiunAPIException(VidiunErrors::INVALID_FLAVOR_ASSET_ID, $flavorId);

		if(!myEntryUtils::isFlavorSupportedByPackager($flavorAsset, false))
			throw new VidiunAPIException(VidiunErrors::GIVEN_ID_NOT_SUPPORTED);

		$content = myEntryUtils::getVolumeMapContent($flavorAsset);
		return $content;
	}
}


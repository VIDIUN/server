<?php
/**
 * Base Entry Service
 *
 * @service baseEntry
 * @package api
 * @subpackage services
 */
class BaseEntryService extends VidiunEntryService
{
    /* (non-PHPdoc)
     * @see VidiunEntryService::initService()
     */
    public function initService($serviceId, $serviceName, $actionName)
    {
	    parent::initService($serviceId, $serviceName, $actionName);
        $partner = PartnerPeer::retrieveByPK($this->getPartnerId());
        if ($actionName == "anonymousRank" && $partner->getEnabledService(VidiunPermissionName::FEATURE_LIKE))
        {
            throw new VidiunAPIException(VidiunErrors::ACTION_FORBIDDEN, "anonymousRank");
        }
    }

	/* (non-PHPdoc)
	 * @see VidiunBaseService::vidiunNetworkAllowed()
	 */
	protected function vidiunNetworkAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		if ($actionName === 'getContextData') {
			return true;
		}
		if($actionName == 'getPlaybackContext'){
			return true;
		}

		return parent::vidiunNetworkAllowed($actionName);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerRequired()
	 */
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'flag') {
			return false;
		}
		return parent::partnerRequired($actionName);
	}
	
    /**
     * Generic add entry, should be used when the uploaded entry type is not known.
     *
     * @action add
     * @param VidiunBaseEntry $entry
     * @param VidiunEntryType $type
     * @return VidiunBaseEntry
     * @throws VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED
     */
    function addAction(VidiunBaseEntry $entry, $type = -1)
    {
    	if($type && $type != VidiunEntryType::AUTOMATIC)
    		$entry->type = $type;

		myEntryUtils::verifyEntryType($entry);

    	$dbEntry = parent::add($entry, $entry->conversionProfileId);
    	if($dbEntry->getStatus() != entryStatus::READY)
    	{
	   		$dbEntry->setStatus(entryStatus::NO_CONTENT);
	    	$dbEntry->save();
    	}
    	
		$trackEntry = new TrackEntry();
		$trackEntry->setEntryId($dbEntry->getId());
		$trackEntry->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY);
		$trackEntry->setDescription(__METHOD__ . ":" . __LINE__ . "::ENTRY_BASE");
		TrackEntry::addTrackEntry($trackEntry);
		
    	myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry, $dbEntry->getPartnerId(), null, null, null, $dbEntry->getId());
    	
	    $entry->fromObject($dbEntry, $this->getResponseProfile());
	    return $entry;
    }
	
    /**
     * Attach content resource to entry in status NO_MEDIA
     *
     * @action addContent
	 * @param string $entryId
     * @param VidiunResource $resource
     * @return VidiunBaseEntry
     * @throws VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED
     * @validateUser entry entryId edit
     */
    function addContentAction($entryId, VidiunResource $resource)
    {
    	$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
	
		if ($dbEntry->getStatus() != entryStatus::NO_CONTENT)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ALREADY_WITH_CONTENT);
		
		$vResource = $resource->toObject();
    	if($dbEntry->getType() == VidiunEntryType::AUTOMATIC || is_null($dbEntry->getType()))
    		$this->setEntryTypeByResource($dbEntry, $vResource);
		$dbEntry->save();
		
		$resource->validateEntry($dbEntry);
		$this->attachResource($vResource, $dbEntry);
		$this->validateContent($dbEntry);
		$resource->entryHandled($dbEntry);
    	
		return $this->getEntry($entryId);
    }

    /**
     * @param vResource $resource
     * @param entry $dbEntry
     * @param asset $asset
     */
    protected function attachResource(vResource $resource, entry $dbEntry, asset $asset = null)
    {
    	$service = null;
    	switch($dbEntry->getType())
    	{
			case entryType::MEDIA_CLIP:
				$service = new MediaService();
    			$service->initService('media', 'media', $this->actionName);
    			break;
				
			case entryType::MIX:
				$service = new MixingService();
    			$service->initService('mixing', 'mixing', $this->actionName);
    			break;
				
			case entryType::PLAYLIST:
				$service = new PlaylistService();
    			$service->initService('playlist', 'playlist', $this->actionName);
    			break;
				
			case entryType::DATA:
				$service = new DataService();
    			$service->initService('data', 'data', $this->actionName);
    			break;
				
			case entryType::LIVE_STREAM:
				$service = new LiveStreamService();
    			$service->initService('liveStream', 'liveStream', $this->actionName);
    			break;
    			
    		default:
    			throw new VidiunAPIException(VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED, $dbEntry->getType());
    	}
    		
    	$service->attachResource($resource, $dbEntry, $asset);
    }
    
    /**
     * @param vResource $resource
     */
    protected function setEntryTypeByResource(entry $dbEntry, vResource $resource)
    {
    	$fullPath = null;
    	switch($resource->getType())
    	{
    		case 'vAssetParamsResourceContainer':
    			return $this->setEntryTypeByResource($dbEntry, $resource->getResource());
    			
			case 'vAssetsParamsResourceContainers':
    			return $this->setEntryTypeByResource($dbEntry, reset($resource->getResources()));
				
			case 'vFileSyncResource':
				$sourceEntry = null;
		    	if($resource->getFileSyncObjectType() == FileSyncObjectType::ENTRY)
		    		$sourceEntry = entryPeer::retrieveByPK($resource->getObjectId());
		    	if($resource->getFileSyncObjectType() == FileSyncObjectType::FLAVOR_ASSET)
		    	{
		    		$sourceAsset = assetPeer::retrieveByPK($resource->getObjectId());
		    		if($sourceAsset)
		    			$sourceEntry = $sourceAsset->getentry();
		    	}
		    	
				if($sourceEntry)
				{
					$dbEntry->setType($sourceEntry->getType());
					$dbEntry->setMediaType($sourceEntry->getMediaType());
				}
				return;
				
			case 'vLocalFileResource':
				$fullPath = $resource->getLocalFilePath();
				break;
				
			case 'vUrlResource':
			case 'vRemoteStorageResource':
				$fullPath = $resource->getUrl();
				break;
				
			default:
				return;
    	}
    	if($fullPath)
    		$this->setEntryTypeByExtension($dbEntry, $fullPath);
    }
    
    protected function setEntryTypeByExtension(entry $dbEntry, $fullPath)
    {
    	$ext = pathinfo($fullPath, PATHINFO_EXTENSION);

    	if(!$ext)
		{
			$ext = myFileUploadService::getExtensionByContentType($fullPath);
			if(!$ext)
				return;
		}
    	
    	$mediaType = myFileUploadService::getMediaTypeFromFileExt($ext);
    	if($mediaType != entry::ENTRY_MEDIA_TYPE_AUTOMATIC)
    	{
			$dbEntry->setType(entryType::MEDIA_CLIP);
			$dbEntry->setMediaType($mediaType);
    	}
    }
    
    /**
     * Generic add entry using an uploaded file, should be used when the uploaded entry type is not known.
     *
     * @action addFromUploadedFile
     * @param VidiunBaseEntry $entry
     * @param string $uploadTokenId
     * @param VidiunEntryType $type
     * @return VidiunBaseEntry
     */
    function addFromUploadedFileAction(VidiunBaseEntry $entry, $uploadTokenId, $type = -1)
    {
		try
	    {
	    	// check that the uploaded file exists
			$entryFullPath = vUploadTokenMgr::getFullPathByUploadTokenId($uploadTokenId);
		}
		catch(vCoreException $ex)
		{
			if ($ex->getCode() == vUploadTokenException::UPLOAD_TOKEN_INVALID_STATUS)
			{
				throw new VidiunAPIException(VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY);
			}
			throw($ex);
		}

		if (!file_exists($entryFullPath))
		{
			// Backward compatability - support case in which the required file exist in the other DC
			vFileUtils::dumpApiRequest ( vDataCenterMgr::getRemoteDcExternalUrlByDcId ( 1 - vDataCenterMgr::getCurrentDcId () ) );
			/*
			$remoteDCHost = vUploadTokenMgr::getRemoteHostForUploadToken($uploadTokenId, vDataCenterMgr::getCurrentDcId());
			if($remoteDCHost)
			{
				vFileUtils::dumpApiRequest($remoteDCHost);
			}
			else
			{
				throw new VidiunAPIException(VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN);
			}
			*/
		}
	    
	    // validate the input object
	    //$entry->validatePropertyMinLength("name", 1);
	    if (!$entry->name)
		    $entry->name = $this->getPartnerId().'_'.time();

	    // first copy all the properties to the db entry, then we'll check for security stuff
	    $dbEntry = $this->duplicateTemplateEntry($entry->conversionProfileId, $entry->templateEntryId);
	    $dbEntry = $entry->toInsertableObject($dbEntry);
	    

	    
	    $dbEntry->setType($type);
	    $dbEntry->setMediaType(entry::ENTRY_MEDIA_TYPE_AUTOMATIC);
	        
	    $this->checkAndSetValidUserInsert($entry, $dbEntry);
	    $this->checkAdminOnlyInsertProperties($entry);
	    $this->validateAccessControlId($entry);
	    $this->validateEntryScheduleDates($entry, $dbEntry);
	    
	    $dbEntry->setPartnerId($this->getPartnerId());
	    $dbEntry->setSubpId($this->getPartnerId() * 100);
	    $dbEntry->setSourceId( $uploadTokenId );
	    $dbEntry->setSourceLink( $entryFullPath );
	    myEntryUtils::setEntryTypeAndMediaTypeFromFile($dbEntry, $entryFullPath);
	    $dbEntry->setDefaultModerationStatus();
		
		// hack due to VCW of version  from VMC
		if (! is_null ( parent::getConversionQualityFromRequest () ))
			$dbEntry->setConversionQuality ( parent::getConversionQualityFromRequest () );
		
	    $dbEntry->save();
	    
	    $vshow = $this->createDummyVShow();
	    $vshowId = $vshow->getId();
	    
	    // setup the needed params for my insert entry helper
	    $paramsArray = array (
		    "entry_media_source" => VidiunSourceType::FILE,
		    "entry_media_type" => $dbEntry->getMediaType(),
		    "entry_full_path" => $entryFullPath,
		    "entry_license" => $dbEntry->getLicenseType(),
		    "entry_credit" => $dbEntry->getCredit(),
		    "entry_source_link" => $dbEntry->getSourceLink(),
		    "entry_tags" => $dbEntry->getTags(),
	    );
			
	    $token = $this->getVsUniqueString();
	    $insert_entry_helper = new myInsertEntryHelper(null , $dbEntry->getVuserId(), $vshowId, $paramsArray);
	    $insert_entry_helper->setPartnerId($this->getPartnerId(), $this->getPartnerId() * 100);
	    $insert_entry_helper->insertEntry($token, $dbEntry->getType(), $dbEntry->getId(), $dbEntry->getName(), $dbEntry->getTags(), $dbEntry);
	    $dbEntry = $insert_entry_helper->getEntry();
	    
	    vUploadTokenMgr::closeUploadTokenById($uploadTokenId);
	    
	    myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry);

	    $entry->fromObject($dbEntry, $this->getResponseProfile());
	    return $entry;
    }
    
	/**
	 * Get base entry by ID.
	 *
	 * @action get
	 * @param string $entryId Entry id
	 * @param int $version Desired version of the data
	 * @return VidiunBaseEntry The requested entry
	 */
    function getAction($entryId, $version = -1)
    {
    	return $this->getEntry($entryId, $version);
    }

    /**
     * Get remote storage existing paths for the asset.
     *
     * @action getRemotePaths
     * @param string $entryId
     * @return VidiunRemotePathListResponse
     * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
     * @throws VidiunErrors::ENTRY_NOT_READY
     */
    public function getRemotePathsAction($entryId)
    {
		return $this->getRemotePaths($entryId);
	}
	
	/**
	 * Update base entry. Only the properties that were set will be updated.
	 *
	 * @action update
	 * @param string $entryId Entry id to update
	 * @param VidiunBaseEntry $baseEntry Base entry metadata to update
	 * @param VidiunResource $resource Resource to be used to replace entry content
	 * @return VidiunBaseEntry The updated entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry entryId edit
	 */
	function updateAction($entryId, VidiunBaseEntry $baseEntry)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		myEntryUtils::verifyEntryType($baseEntry);

		$baseEntry = $this->updateEntry($entryId, $baseEntry);
		return $baseEntry;
	}
	
	/**
	 * Update the content resource associated with the entry.
	 *
	 * @action updateContent
	 * @param string $entryId Entry id to update
	 * @param VidiunResource $resource Resource to be used to replace entry content
	 * @param int $conversionProfileId The conversion profile id to be used on the entry
	 * @param VidiunEntryReplacementOptions $advancedOptions Additional update content options
	 * @return VidiunBaseEntry The updated entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry entryId edit
	 */
	function updateContentAction($entryId, VidiunResource $resource, $conversionProfileId = null, $advancedOptions = null)
	{
    	$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
	
	 	if($dbEntry->getType() == VidiunEntryType::AUTOMATIC || is_null($dbEntry->getType()))
        {
        	$vResource = $resource->toObject();
        	$this->setEntryTypeByResource($dbEntry, $vResource);
        	$dbEntry->save();
        }
		
		$baseEntry = new VidiunBaseEntry();
		$baseEntry->fromObject($dbEntry, $this->getResponseProfile());
		
		switch($dbEntry->getType())
    	{
			case entryType::MEDIA_CLIP:
				$service = new MediaService();
    			$service->initService('media', 'media', $this->actionName);
				try
				{
					$service->replaceResource($resource, $dbEntry, $conversionProfileId, $advancedOptions);
					$this->validateContent($dbEntry);
				}
				catch (vCoreException $e)
				{
					if ($e->getCode()==vCoreException::SOURCE_FILE_NOT_FOUND)
						throw new VidiunAPIException(APIErrors::SOURCE_FILE_NOT_FOUND);
				}
		    	$baseEntry->fromObject($dbEntry, $this->getResponseProfile());
    			return $baseEntry;
			case entryType::MIX:
			case entryType::PLAYLIST:
			case entryType::DATA:
			case entryType::LIVE_STREAM:
    		default:
    			// TODO load from plugin manager other entry services such as document
    			throw new VidiunAPIException(VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED, $baseEntry->type);
    	}
    	
    	return $baseEntry;
	}
	
	/**
	 * Get an array of VidiunBaseEntry objects by a comma-separated list of ids.
	 *
	 * @action getByIds
	 * @param string $entryIds Comma separated string of entry ids
	 * @return VidiunBaseEntryArray Array of base entry ids
	 */
	function getByIdsAction($entryIds)
	{
		$entryIdsArray = explode(",", $entryIds);
		
		// remove white spaces
		foreach($entryIdsArray as &$entryId)
			$entryId = trim($entryId);
			
	 	$list = entryPeer::retrieveByPKs($entryIdsArray);
		$newList = array();
		
		$vs = $this->getVs();
		$isAdmin = false;
		if($vs)
			$isAdmin = $vs->isAdmin();
			
	 	foreach($list as $dbEntry)
	 	{
	 		$entry = VidiunEntryFactory::getInstanceByType($dbEntry->getType(), $isAdmin);
		    $entry->fromObject($dbEntry, $this->getResponseProfile());
		    $newList[] = $entry;
	 	}
	 	
	 	return $newList;
	}

	/**
	 * Delete an entry.
	 *
	 * @action delete
	 * @param string $entryId Entry id to delete
	 * @validateUser entry entryId edit ownerOnly
	 */
	function deleteAction($entryId)
	{
		$this->deleteEntry($entryId);
	}
	
	/**
	 * List base entries by filter with paging support.
	 *
	 * @action list
     * @param VidiunBaseEntryFilter $filter Entry filter
	 * @param VidiunFilterPager $pager Pager
	 * @return VidiunBaseEntryListResponse Wrapper for array of base entries and total count
	 */
	function listAction(VidiunBaseEntryFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
		{
			$filter = new VidiunBaseEntryFilter();
		}
			
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}

		$result = $filter->getListResponse($pager, $this->getResponseProfile());
		
		if ($result->totalCount == 1 && 
			count($result->objects) == 1 && 
			$result->objects[0]->status != VidiunEntryStatus::READY)
		{
			// the purpose of this is to solve a case in which a player attempts to play a non-ready entry, 
			// and the request becomes cached for a long time, preventing playback even after the entry
			// becomes ready
			vApiCache::setExpiry(60);
		}
		
		// NOTE: The following is a hack in order to make sure all responses are of type VidiunBaseEntryListResponse.
		//       The reason is that baseentry::list() is not being extended by derived classes.
		$response = new VidiunBaseEntryListResponse();
		$response->objects = $result->objects;
		$response->totalCount = $result->totalCount;
		return $response;
	}
	
	/**
	 * List base entries by filter according to reference id
	 *
	 * @action listByReferenceId
	 * @param string $refId Entry Reference ID
	 * @param VidiunFilterPager $pager Pager
	 * @throws VidiunErrors::MISSING_MANDATORY_PARAMETER
	 * @return VidiunBaseEntryListResponse Wrapper for array of base entries and total count
	 */
	function listByReferenceId($refId, VidiunFilterPager $pager = null)
	{
		if (!$refId)
		{
			//if refId wasn't provided return an error of missing parameter
			throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, $refId);
		}
				
		if (!$pager){
			$pager = new VidiunFilterPager();
		}
		$entryFilter = new entryFilter();
		$entryFilter->setPartnerSearchScope(baseObjectFilter::MATCH_VIDIUN_NETWORK_AND_PRIVATE);
		//setting reference ID
		$entryFilter->set('_eq_reference_id', $refId);
		$c = VidiunCriteria::create(entryPeer::OM_CLASS);
		$pager->attachToCriteria($c);
		$entryFilter->attachToCriteria($c);
		$c->add(entryPeer::DISPLAY_IN_SEARCH, mySearchUtils::DISPLAY_IN_SEARCH_SYSTEM, Criteria::NOT_EQUAL);
				
		VidiunCriterion::disableTag(VidiunCriterion::TAG_WIDGET_SESSION);

		if (vEntitlementUtils::getEntitlementEnforcement() && !vCurrentContext::$is_admin_session && entryPeer::getUserContentOnly())
			entryPeer::setFilterResults(true);

		$list = entryPeer::doSelect($c);
		VidiunCriterion::restoreTag(VidiunCriterion::TAG_WIDGET_SESSION);
		
		$totalCount = $c->getRecordsCount();
				
	    $newList = VidiunBaseEntryArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunBaseEntryListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/**
	 * Count base entries by filter.
	 *
	 * @action count
     * @param VidiunBaseEntryFilter $filter Entry filter
	 * @return int
	 */
	function countAction(VidiunBaseEntryFilter $filter = null)
	{
	    return parent::countEntriesByFilter($filter);
	}
	
	/**
	 * Upload a file to Vidiun, that can be used to create an entry.
	 *
	 * @action upload
	 * @param file $fileData The file data
	 * @return string Upload token id
	 *
	 * @deprecated use upload.upload or uploadToken.add instead
	 */
	function uploadAction($fileData)
	{
		$vsUnique = $this->getVsUniqueString();
		
		$uniqueId = substr(base_convert(md5(uniqid(rand(), true)), 16, 36), 1, 20);
		
		$ext = pathinfo($fileData["name"], PATHINFO_EXTENSION);
		$token = $vsUnique."_".$uniqueId.".".$ext;
		// filesync ok
		$res = myUploadUtils::uploadFileByToken($fileData, $token, "", null, true);
	
		return $res["token"];
	}
	
	/**
	 * Update entry thumbnail using a raw jpeg file.
	 *
	 * @action updateThumbnailJpeg
	 * @param string $entryId Media entry id
	 * @param file $fileData Jpeg file data
	 * @return VidiunBaseEntry The media entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 */
	function updateThumbnailJpegAction($entryId, $fileData)
	{
		return parent::updateThumbnailJpegForEntry($entryId, $fileData);
	}
	
	/**
	 * Update entry thumbnail using URL.
	 *
	 * @action updateThumbnailFromUrl
	 * @param string $entryId Media entry id
	 * @param string $url file url
	 * @return VidiunBaseEntry The media entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 */
	function updateThumbnailFromUrlAction($entryId, $url)
	{
		return parent::updateThumbnailForEntryFromUrl($entryId, $url);
	}
	
	/**
	 * Update entry thumbnail from a different entry by a specified time offset (in seconds).
	 *
	 * @action updateThumbnailFromSourceEntry
	 * @param string $entryId Media entry id
	 * @param string $sourceEntryId Media entry id
	 * @param int $timeOffset Time offset (in seconds)
	 * @return VidiunBaseEntry The media entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 */
	function updateThumbnailFromSourceEntryAction($entryId, $sourceEntryId, $timeOffset)
	{
		return parent::updateThumbnailForEntryFromSourceEntry($entryId, $sourceEntryId, $timeOffset);
	}
	
	/**
	 * Flag inappropriate entry for moderation.
	 *
	 * @action flag
	 * @param string $entryId
	 * @param VidiunModerationFlag $moderationFlag
	 * @vsOptional
	 *
 	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function flagAction(VidiunModerationFlag $moderationFlag)
	{
		VidiunResponseCacher::disableCache();
		return parent::flagEntry($moderationFlag);
	}
	
	/**
	 * Reject the entry and mark the pending flags (if any) as moderated (this will make the entry non-playable).
	 *
	 * @action reject
	 * @param string $entryId
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function rejectAction($entryId)
	{
		parent::rejectEntry($entryId);
	}
	
	/**
	 * Approve the entry and mark the pending flags (if any) as moderated (this will make the entry playable).
	 *
	 * @action approve
	 * @param string $entryId
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function approveAction($entryId)
	{
		parent::approveEntry($entryId);
	}
	
	/**
	 * List all pending flags for the entry.
	 *
	 * @action listFlags
	 * @param string $entryId
	 * @param VidiunFilterPager $pager
	 *
	 * @return VidiunModerationFlagListResponse
	 */
	public function listFlags($entryId, VidiunFilterPager $pager = null)
	{
		return parent::listFlagsForEntry($entryId, $pager);
	}
	
	/**
	 * Anonymously rank an entry, no validation is done on duplicate rankings.
	 *
	 * @action anonymousRank
	 * @param string $entryId
	 * @param int $rank
	 */
	public function anonymousRankAction($entryId, $rank)
	{
		VidiunResponseCacher::disableCache();
		return parent::anonymousRankEntry($entryId, null, $rank);
	}
	
	/**
	 * This action delivers entry-related data, based on the user's context: access control, restriction, playback format and storage information.
	 * @action getContextData
	 * @param string $entryId
	 * @param VidiunEntryContextDataParams $contextDataParams
	 * @return VidiunEntryContextDataResult
	 */
	public function getContextData($entryId, VidiunEntryContextDataParams $contextDataParams)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		if ($dbEntry->getStatus() != entryStatus::READY)
		{
			// the purpose of this is to solve a case in which a player attempts to play a non-ready entry,
			// and the request becomes cached for a long time, preventing playback even after the entry
			// becomes ready
			vApiCache::setExpiry(60);
		}
		
		$asset = null;
		if($contextDataParams->flavorAssetId)
		{
			$asset = assetPeer::retrieveById($contextDataParams->flavorAssetId);
			if(!$asset)
				throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $contextDataParams->flavorAssetId);
		}
			
		$contextDataHelper = new vContextDataHelper($dbEntry, $this->getPartner(), $asset);
		
		if ($dbEntry->getAccessControl() && $dbEntry->getAccessControl()->hasRules())
			$accessControlScope = $dbEntry->getAccessControl()->getScope();
		else
			$accessControlScope = new accessControlScope();
		$contextDataParams->toObject($accessControlScope);
		
		$contextDataHelper->buildContextDataResult($accessControlScope, $contextDataParams->flavorTags, $contextDataParams->streamerType, $contextDataParams->mediaProtocol);
		if($contextDataHelper->getDisableCache())
			VidiunResponseCacher::disableCache();
			
		$result = new VidiunEntryContextDataResult();
		$result->fromObject($contextDataHelper->getContextDataResult());
		$result->flavorAssets = VidiunFlavorAssetArray::fromDbArray($contextDataHelper->getAllowedFlavorAssets());
		$result->msDuration = $contextDataHelper->getMsDuration();
		$result->streamerType = $contextDataHelper->getStreamerType();
		$result->mediaProtocol = $contextDataHelper->getMediaProtocol();
		$result->storageProfilesXML = $contextDataHelper->getStorageProfilesXML();
		$result->isAdmin = $contextDataHelper->getIsAdmin();
		
		$parentEntryId = $dbEntry->getSecurityParentId();
		if ($parentEntryId)
		{
			$dbEntry = $dbEntry->getParentEntry();
			if(!$dbEntry)
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $parentEntryId);
		}
		
		$result->isScheduledNow = $dbEntry->isScheduledNow($contextDataParams->time);
		if (!($result->isScheduledNow) && $this->getVs() ){
			// in case the sview is defined in the vs simulate schedule now true to allow player to pass verification
			if ( $this->getVs()->verifyPrivileges(vs::PRIVILEGE_VIEW, vs::PRIVILEGE_WILDCARD) ||
				$this->getVs()->verifyPrivileges(vs::PRIVILEGE_VIEW, $entryId)) {
				$result->isScheduledNow = true;
			}
		}

        $result->pluginData = new VidiunPluginDataArray();
        $pluginInstances = VidiunPluginManager::getPluginInstances('IVidiunEntryContextDataContributor');
        foreach ($pluginInstances as $pluginInstance)
        {
            $pluginDataCore = $pluginInstance->contributeToEntryContextDataResult($dbEntry, $accessControlScope, $contextDataHelper);
	        if (!is_null($pluginDataCore))
	        {
		        $pluginDataApi = VidiunPluginManager::loadObject('VidiunPluginData', $pluginInstance->getPluginName());
		        $pluginDataApi->fromObject($pluginDataCore);
		        $result->pluginData[get_class($pluginDataApi)] = $pluginDataApi;
	        }
        }

		return $result;
	}
	
	/**
	 * @action export
	 * Action for manually exporting an entry
	 * @param string $entryId
	 * @param int $storageProfileId
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @return VidiunBaseEntry The exported entry
	 */
	public function exportAction ( $entryId , $storageProfileId )
	{
	    $dbEntry = entryPeer::retrieveByPK($entryId);
	    if (!$dbEntry)
	    {
	        throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
	    }
	    
	    $dbStorageProfile = StorageProfilePeer::retrieveByPK($storageProfileId);
	    if (!$dbStorageProfile)
	    {
	        throw new VidiunAPIException(VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND, $storageProfileId);
	    }
	    
	    $scope = $dbStorageProfile->getScope();
	    $scope->setEntryId($entryId);
	    if(!$dbStorageProfile->fulfillsRules($scope))
	    {
	    	throw new VidiunAPIException(VidiunErrors::STORAGE_PROFILE_RULES_NOT_FULFILLED, $storageProfileId);
	    }

	    try
	    {
	    	vStorageExporter::exportEntry($dbEntry, $dbStorageProfile);
	    }
	    catch (vCoreException $e)
	    {
	    	if ($e->getCode()==vCoreException::PROFILE_STATUS_DISABLED)
	    	{
 	    		throw new VidiunAPIException(APIErrors::PROFILE_STATUS_DISABLED,$entryId);
	    	}
	    }
	     
	    
	    //TODO: implement export errors
	    
		$entry = VidiunEntryFactory::getInstanceByType($dbEntry->getType());
		$entry->fromObject($dbEntry, $this->getResponseProfile());
	    return $entry;
	    
	}
	
	/**
	 * Index an entry by id.
	 *
	 * @action index
	 * @param string $id
	 * @param bool $shouldUpdate
	 * @return int entry int id
	 */
	function indexAction($id, $shouldUpdate = true)
	{
		if(vEntitlementUtils::getEntitlementEnforcement())
			throw new VidiunAPIException(VidiunErrors::CANNOT_INDEX_OBJECT_WHEN_ENTITLEMENT_IS_ENABLE);
			
		$entryDb = entryPeer::retrieveByPK($id);
		if (!$entryDb)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $id);

		if (!$shouldUpdate)
		{
			$entryDb->indexToSearchIndex();
			
			return $entryDb->getIntId();
		}
		
		return myEntryUtils::index($entryDb);
	}

	/**
	 * Clone an entry with optional attributes to apply to the clone
	 * 
	 * @action clone
	 * @param string $entryId Id of entry to clone
	 * @param VidiunBaseEntryCloneOptionsArray $cloneOptions
	 * @param VidiunBaseEntry $updateEntry [optional] Attributes from these entry will be updated into the cloned entry
	 * @return VidiunBaseEntry The cloned entry
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function cloneAction( $entryId, $cloneOptions=null )
	{
		if(vCurrentContext::$vs_partner_id == Partner::BATCH_PARTNER_ID)
		{
			entryPeer::setUseCriteriaFilter(false);
			categoryEntryPeer::setUseCriteriaFilter(false);
		}

		// Get the entry
		$coreEntry = entryPeer::retrieveByPK( $entryId );
		if ( ! $coreEntry )
		{
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		}
//		$coreClonedOptionsArray = array();
//		foreach ($cloneOptions as $item)
//		{
//			$coreClonedOptionsArray[] = $item->toObject();
//		}

		$coreClonedOptionsArray = $cloneOptions->toObjectsArray();

		// Copy the entry into a new one based on the given partner data.
		$clonedEntry = myEntryUtils::copyEntry($coreEntry, $this->getPartner(), $coreClonedOptionsArray);

		return $this->getEntry($clonedEntry->getId());
	}

	/**
	 * This action delivers all data relevant for player
	 * @action getPlaybackContext
	 * @param string $entryId
	 * @param VidiunPlaybackContextOptions $contextDataParams
	 * @return VidiunPlaybackContext
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function getPlaybackContextAction($entryId, VidiunPlaybackContextOptions $contextDataParams)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ($dbEntry->getStatus() != entryStatus::READY)
		{
			// the purpose of this is to solve a case in which a player attempts to play a non-ready entry,
			// and the request becomes cached for a long time, preventing playback even after the entry
			// becomes ready
			vApiCache::setExpiry(60);
		}

		$parentEntryId = $dbEntry->getSecurityParentId();
		if ($parentEntryId)
		{
			$dbEntry = $dbEntry->getParentEntry();
			if(!$dbEntry)
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $parentEntryId);
		}

		$asset = null;
		if ($contextDataParams->flavorAssetId)
		{
			$asset = assetPeer::retrieveById($contextDataParams->flavorAssetId);
			if (!$asset)
				throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $contextDataParams->flavorAssetId);
		}

		$contextDataHelper = new vContextDataHelper($dbEntry, $this->getPartner(), $asset);

		if ($dbEntry->getAccessControl() && $dbEntry->getAccessControl()->hasRules())
			$accessControlScope = $dbEntry->getAccessControl()->getScope();
		else
			$accessControlScope = new accessControlScope();
		$contextDataParams->toObject($accessControlScope);

		$contextDataHelper->buildContextDataResult($accessControlScope, vContextDataHelper::ALL_TAGS, $contextDataParams->streamerType, $contextDataParams->mediaProtocol, true);
		if ($contextDataHelper->getDisableCache())
			VidiunResponseCacher::disableCache();

		$isScheduledNow = $dbEntry->isScheduledNow($contextDataParams->time);
		if (!($isScheduledNow) && $this->getVs() ){
			// in case the sview is defined in the vs simulate schedule now true to allow player to pass verification
			if ( $this->getVs()->verifyPrivileges(vs::PRIVILEGE_VIEW, vs::PRIVILEGE_WILDCARD) ||
				$this->getVs()->verifyPrivileges(vs::PRIVILEGE_VIEW, $entryId)) {
				$isScheduledNow = true;
			}
		}

		$contextDataHelper->setMediaProtocol($contextDataParams->mediaProtocol);
		$contextDataHelper->setStreamerType($contextDataParams->streamerType);

		$playbackContextDataHelper = new vPlaybackContextDataHelper();
		$playbackContextDataHelper->setIsScheduledNow($isScheduledNow);
		$playbackContextDataHelper->constructPlaybackContextResult($contextDataHelper, $dbEntry);

		$result = new VidiunPlaybackContext();
		$result->fromObject($playbackContextDataHelper->getPlaybackContext());
		$result->actions = VidiunRuleActionArray::fromDbArray($contextDataHelper->getContextDataResult()->getActions());

		return $result;
	}

}

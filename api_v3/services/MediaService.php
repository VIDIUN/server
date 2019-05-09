<?php

/**
 * Media service lets you upload and manage media files (images / videos & audio)
 *
 * @service media
 * @package api
 * @subpackage services
 */
class MediaService extends VidiunEntryService
{
	protected function vidiunNetworkAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		if ($actionName === 'convert') {
			return true;
		}
		if ($actionName === 'addFromEntry') {
			return true;
		}
		if ($actionName === 'addFromFlavorAsset') {
			return true;
		}
		if ($actionName === 'addContent') {
			return true;
		}
		if ($actionName === 'updateContent') {
			return true;
		}

		// admin and batch
		if ($actionName === 'list' && vCurrentContext::$master_partner_id < 0) {
			return true;
		}

		return parent::vidiunNetworkAllowed($actionName);
	}

	protected function partnerRequired($actionName)
	{
		if ($actionName === 'flag') {
			return false;
		}
		return parent::partnerRequired($actionName);
	}

    /**
     * Add entry
     *
     * @action add
     * @param VidiunMediaEntry $entry
     * @return VidiunMediaEntry
     */
    function addAction(VidiunMediaEntry $entry)
    {
    	if($entry->conversionQuality && !$entry->conversionProfileId)
    		$entry->conversionProfileId = $entry->conversionQuality;

    	$dbEntry = parent::add($entry, $entry->conversionProfileId);

    	$entryStatus = entryStatus::NO_CONTENT;

    	if ( PermissionPeer::isValidForPartner(PermissionName::FEATURE_DRAFT_ENTRY_CONV_PROF_SELECTION, $dbEntry->getPartnerId()) )
    	{
	    	$entryConversionProfileHasFlavors = myPartnerUtils::entryConversionProfileHasFlavors( $dbEntry->getId() );
	    	if ( ! $entryConversionProfileHasFlavors )
	    	{
		    	// If the entry's conversion profile dones't contain any flavors, mark the entry as READY
	    		$entryStatus = entryStatus::READY;
	    	}
    	}
    	
    	$dbEntry->setStatus( $entryStatus );

		$dbEntry->save();

		$trackEntry = new TrackEntry();
		$trackEntry->setEntryId($dbEntry->getId());
		$trackEntry->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY);
		$trackEntry->setDescription(__METHOD__ . ":" . __LINE__ . "::ENTRY_MEDIA");
		TrackEntry::addTrackEntry($trackEntry);

    	myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry, $dbEntry->getPartnerId(), null, null, null, $dbEntry->getId());

		$entry->fromObject($dbEntry, $this->getResponseProfile());
		return $entry;
    }

    protected function shoudlValidateLocal()
	{
		//if multi request of more than one api call
		return  (vCurrentContext::$multiRequest_index <= 1);
	}

    /**
     * Add content to media entry which is not yet associated with content (therefore is in status NO_CONTENT).
     * If the requirement is to replace the entry's associated content, use action updateContent.
     *
     * @action addContent
     * @param string $entryId
     * @param VidiunResource $resource
     * @return VidiunMediaEntry
     * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
     * @throws VidiunErrors::ENTRY_ALREADY_WITH_CONTENT
     * @validateUser entry entryId edit
     */
	function addContentAction($entryId, VidiunResource $resource = null)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

	    if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::MEDIA_CLIP)
		    throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ($dbEntry->getStatus() != entryStatus::NO_CONTENT)
		    throw new VidiunAPIException(VidiunErrors::ENTRY_ALREADY_WITH_CONTENT);
		
	    if ($resource)
	    {
			try
			{
				$validateLocalExist = $this->shoudlValidateLocal();
				$resource->validateEntry($dbEntry, $validateLocalExist);
				$vResource = $resource->toObject();
				$this->attachResource($vResource, $dbEntry);
			}
		    catch (Exception $e)
		    {
			    $this->handleErrorDuringSetResource($entryId, $e, $resource);
		    }
		    $this->validateContent($dbEntry);
		       $resource->entryHandled($dbEntry);
	    }
	    return $this->getEntry($entryId);
    }

    /**
     * @param VidiunResource $resource
     * @param entry $dbEntry
     * @param int $conversionProfileId
     */
    protected function replaceResource(VidiunResource $resource, entry $dbEntry, $conversionProfileId = null, $advancedOptions = null)
    {
	    if($advancedOptions)
    	{
    		$dbEntry->setReplacementOptions($advancedOptions->toObject());
    		$dbEntry->save();
    	}
		if($dbEntry->getStatus() == VidiunEntryStatus::NO_CONTENT || $dbEntry->getMediaType() == VidiunMediaType::IMAGE)
		{
			$resource->validateEntry($dbEntry, true);

			if($conversionProfileId)
			{
				$dbEntry->setConversionQuality($conversionProfileId);
				$dbEntry->save();
			}

			$vResource = $resource->toObject();
			$this->attachResource($vResource, $dbEntry);
		}
		else
		{
			$vResource = $resource->toObject();
			$tempMediaEntry = new VidiunMediaEntry();
			$tempMediaEntry->type = $dbEntry->getType();
			$tempMediaEntry->mediaType = $dbEntry->getMediaType();
			$tempMediaEntry->sourceType = $dbEntry->getSourceType();
			$tempMediaEntry->streams = $dbEntry->getStreams();

			if ( !$conversionProfileId ) {
				$originalConversionProfileId = $dbEntry->getConversionQuality();
				$conversionProfile = conversionProfile2Peer::retrieveByPK($originalConversionProfileId);
				if ( is_null($conversionProfile) || $conversionProfile->getType() != ConversionProfileType::MEDIA )
				{
					$defaultConversionProfile = myPartnerUtils::getConversionProfile2ForPartner( $this->getPartnerId() );
					if ( !is_null($defaultConversionProfile) ) {
						$conversionProfileId = $defaultConversionProfile->getId();
					}
				} else {
					$conversionProfileId = $originalConversionProfileId;
				}
			}
			if($conversionProfileId)
				$tempMediaEntry->conversionProfileId = $conversionProfileId;
			
			if ($conversionProfileId && !$advancedOptions)
			{
				$conversionProfile = conversionProfile2Peer::retrieveByPK($conversionProfileId);
				if($conversionProfile)
				{
					$defaultReplacementOptions = $conversionProfile->getDefaultReplacementOptions(); 
					if ($defaultReplacementOptions) 
					{
						$dbEntry->setReplacementOptions($defaultReplacementOptions);
						$dbEntry->save();
					}
				}
			}

			$this->replaceResourceByEntry($dbEntry, $resource, $tempMediaEntry);
		}
    	$resource->entryHandled($dbEntry);
    }

    /**
     * @param vResource $resource
     * @param entry $dbEntry
     * @param asset $dbAsset
     * @return asset
     */
    protected function attachResource(vResource $resource, entry $dbEntry, asset $dbAsset = null)
    {
    	switch($resource->getType())
    	{
			case 'vAssetsParamsResourceContainers':
				// image entry doesn't support asset params
				if($dbEntry->getMediaType() == VidiunMediaType::IMAGE)
					throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($resource));

				return $this->attachAssetsParamsResourceContainers($resource, $dbEntry);

			case 'vAssetParamsResourceContainer':
				// image entry doesn't support asset params
				if($dbEntry->getMediaType() == VidiunMediaType::IMAGE)
					throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($resource));

				return $this->attachAssetParamsResourceContainer($resource, $dbEntry, $dbAsset);

			case 'vUrlResource':
				return $this->attachUrlResource($resource, $dbEntry, $dbAsset);

			case 'vLocalFileResource':
				return $this->attachLocalFileResource($resource, $dbEntry, $dbAsset);

			case 'vLiveEntryResource':
				return $this->attachLiveEntryResource($resource, $dbEntry, $dbAsset);

			case 'vFileSyncResource':
				return $this->attachFileSyncResource($resource, $dbEntry, $dbAsset);

			case 'vRemoteStorageResource':
			case 'vRemoteStorageResources':
				return $this->attachRemoteStorageResource($resource, $dbEntry, $dbAsset);

			case 'vOperationResource':
				if($dbEntry->getMediaType() == VidiunMediaType::IMAGE)
					throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($resource));

				return $this->attachOperationResource($resource, $dbEntry, $dbAsset);

			default:
				VidiunLog::err("Resource of type [" . get_class($resource) . "] is not supported");
				$dbEntry->setStatus(entryStatus::ERROR_IMPORTING);
				$dbEntry->save();

				throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($resource));
    	}
    }

	/**
	 * Adds new media entry by importing an HTTP or FTP URL.
	 * The entry will be queued for import and then for conversion.
	 * This action should be exposed only to the batches
	 *
	 * @action addFromBulk
	 * @param VidiunMediaEntry $mediaEntry Media entry metadata
	 * @param string $url An HTTP or FTP URL
	 * @param int $bulkUploadId The id of the bulk upload job
	 * @return VidiunMediaEntry The new media entry
	 *
	 * @throws VidiunErrors::PROPERTY_VALIDATION_MIN_LENGTH
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 *
	 * @deprecated use media.add instead
	 */
	function addFromBulkAction(VidiunMediaEntry $mediaEntry, $url, $bulkUploadId)
	{
		return $this->addDbFromUrl($mediaEntry, $url, $bulkUploadId);
	}

	/**
	 * Adds new media entry by importing an HTTP or FTP URL.
	 * The entry will be queued for import and then for conversion.
	 *
	 * @action addFromUrl
	 * @param VidiunMediaEntry $mediaEntry Media entry metadata
	 * @param string $url An HTTP or FTP URL
	 * @return VidiunMediaEntry The new media entry
	 *
	 * @throws VidiunErrors::PROPERTY_VALIDATION_MIN_LENGTH
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 *
	 * @deprecated use media.add instead
	 */
	function addFromUrlAction(VidiunMediaEntry $mediaEntry, $url)
	{
		return $this->addDbFromUrl($mediaEntry, $url);
	}

	private function addDbFromUrl(VidiunMediaEntry $mediaEntry, $url, $bulkUploadId = null)
	{
    	if($mediaEntry->conversionQuality && !$mediaEntry->conversionProfileId)
    		$mediaEntry->conversionProfileId = $mediaEntry->conversionQuality;

		$dbEntry = $this->prepareEntryForInsert($mediaEntry);
		if($bulkUploadId)
			$dbEntry->setBulkUploadId($bulkUploadId);

        $vshowId = $dbEntry->getVshowId();

		// setup the needed params for my insert entry helper
		$paramsArray = array (
			"entry_media_source" => VidiunSourceType::URL,
            "entry_media_type" => $dbEntry->getMediaType(),
			"entry_url" => $url,
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

		myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry, $this->getPartnerId(), null, null, null, $dbEntry->getId());

		// FIXME: need to remove something from cache? in the old code the vshow was removed
		$mediaEntry->fromObject($dbEntry, $this->getResponseProfile());
		return $mediaEntry;
	}

	/**
	 * Adds new media entry by importing the media file from a search provider.
	 * This action should be used with the search service result.
	 *
	 * @action addFromSearchResult
	 * @param VidiunMediaEntry $mediaEntry Media entry metadata
	 * @param VidiunSearchResult $searchResult Result object from search service
	 * @return VidiunMediaEntry The new media entry
	 *
	 * @throws VidiunErrors::PROPERTY_VALIDATION_MIN_LENGTH
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 *
	 * @deprecated use media.add instead
	 */
	function addFromSearchResultAction(VidiunMediaEntry $mediaEntry = null, VidiunSearchResult $searchResult = null)
	{
		if($mediaEntry->conversionQuality && !$mediaEntry->conversionProfileId)
			$mediaEntry->conversionProfileId = $mediaEntry->conversionQuality;

		if ($mediaEntry === null)
			$mediaEntry = new VidiunMediaEntry();

		if ($searchResult === null)
			$searchResult = new VidiunSearchResult();

		// copy the fields from search result if they are missing in media entry
		// this should be checked before prepareEntry method call
		if ($mediaEntry->name === null)
			$mediaEntry->name = $searchResult->title;

		if ($mediaEntry->mediaType === null)
			$mediaEntry->mediaType = $searchResult->mediaType;

        if ($mediaEntry->description === null)
        	$mediaEntry->description = $searchResult->description;

        if ($mediaEntry->creditUrl === null)
        	$mediaEntry->creditUrl = $searchResult->sourceLink;

       	if ($mediaEntry->creditUserName === null)
       		$mediaEntry->creditUserName = $searchResult->credit;

     	if ($mediaEntry->tags === null)
      		$mediaEntry->tags = $searchResult->tags;

     	$searchResult->validatePropertyNotNull("searchSource");

    	$mediaEntry->sourceType = VidiunSourceType::SEARCH_PROVIDER;
     	$mediaEntry->searchProviderType = $searchResult->searchSource;
     	$mediaEntry->searchProviderId = $searchResult->id;

		$dbEntry = $this->prepareEntryForInsert($mediaEntry);
      	$dbEntry->setSourceId( $searchResult->id );

        $vshowId = $dbEntry->getVshowId();

       	// $searchResult->licenseType; // FIXME, No support for licenseType
        // FIXME - no need to clone entry if $dbEntry->getSource() == entry::ENTRY_MEDIA_SOURCE_VIDIUN_USER_CLIPS
		if ($dbEntry->getSource() == entry::ENTRY_MEDIA_SOURCE_VIDIUN ||
			$dbEntry->getSource() == entry::ENTRY_MEDIA_SOURCE_VIDIUN_PARTNER ||
			$dbEntry->getSource() == entry::ENTRY_MEDIA_SOURCE_VIDIUN_PARTNER_VSHOW ||
			$dbEntry->getSource() == entry::ENTRY_MEDIA_SOURCE_VIDIUN_VSHOW ||
			$dbEntry->getSource() == entry::ENTRY_MEDIA_SOURCE_VIDIUN_USER_CLIPS)
		{
			$sourceEntryId = $searchResult->id;
			$copyDataResult = myEntryUtils::copyData($sourceEntryId, $dbEntry);

			if (!$copyDataResult) // will be false when the entry id was not found
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $sourceEntryId);

			$dbEntry->setStatusReady();
			$dbEntry->save();
		}
		else
		{
			// setup the needed params for my insert entry helper
			$paramsArray = array (
				"entry_media_source" => $dbEntry->getSource(),
	            "entry_media_type" => $dbEntry->getMediaType(),
				"entry_url" => $searchResult->url,
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
		}

		myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry);

		$mediaEntry->fromObject($dbEntry, $this->getResponseProfile());
		return $mediaEntry;
	}

	/**
	 * Add new entry after the specific media file was uploaded and the upload token id exists
	 *
	 * @action addFromUploadedFile
	 * @param VidiunMediaEntry $mediaEntry Media entry metadata
	 * @param string $uploadTokenId Upload token id
	 * @return VidiunMediaEntry The new media entry
	 *
	 * @throws VidiunErrors::PROPERTY_VALIDATION_MIN_LENGTH
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 *
	 * @deprecated use media.add instead
	 */
	function addFromUploadedFileAction(VidiunMediaEntry $mediaEntry, $uploadTokenId)
	{
		if($mediaEntry->conversionQuality && !$mediaEntry->conversionProfileId)
			$mediaEntry->conversionProfileId = $mediaEntry->conversionQuality;

		try
		{
		    // check that the uploaded file exists
		    $entryFullPath = vUploadTokenMgr::getFullPathByUploadTokenId($uploadTokenId);
		    
		    // Make sure that the uploads path is not modified by $uploadTokenId (with the value of "../" for example )
		    $entryRootDir = realpath( dirname( $entryFullPath ) );
			$uploadPathBase = realpath( myContentStorage::getFSUploadsPath() );
			if ( strpos( $entryRootDir, $uploadPathBase ) !== 0 ) // Composed path doesn't begin with $uploadPathBase?  
			{
				VidiunLog::err( "uploadTokenId [$uploadTokenId] points outside of uploads directory" );
				throw new VidiunAPIException( VidiunErrors::INVALID_UPLOAD_TOKEN_ID );			
			}
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

		$dbEntry = parent::add($mediaEntry, $mediaEntry->conversionProfileId);

        $vshowId = $dbEntry->getVshowId();

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

		$ret = new VidiunMediaEntry();
		if($dbEntry)
		{
			myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry, $dbEntry->getPartnerId(), null, null, null, $dbEntry->getId());
			$ret->fromObject($dbEntry, $this->getResponseProfile());
		}

		return $ret;
	}

	/**
	 * Add new entry after the file was recorded on the server and the token id exists
	 *
	 * @action addFromRecordedWebcam
	 * @param VidiunMediaEntry $mediaEntry Media entry metadata
	 * @param string $webcamTokenId Token id for the recorded webcam file
	 * @return VidiunMediaEntry The new media entry
	 *
	 * @throws VidiunErrors::PROPERTY_VALIDATION_MIN_LENGTH
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 *
	 * @deprecated use media.add instead
	 */
	function addFromRecordedWebcamAction(VidiunMediaEntry $mediaEntry, $webcamTokenId)
	{
    	if($mediaEntry->conversionQuality && !$mediaEntry->conversionProfileId)
    		$mediaEntry->conversionProfileId = $mediaEntry->conversionQuality;

	    // check that the webcam file exists
	    $content = myContentStorage::getFSContentRootPath();
	    $webcamContentRootDir = $content . "/content/webcam/";
	    $webcamBasePath = $webcamContentRootDir . $webcamTokenId;

	    // Make sure that the root path of the webcam content is not modified by $webcamTokenId (with the value of "../" for example )
	    $webcamContentRootDir = realpath( $webcamContentRootDir );
	    $webcamBaseRootDir = realpath( dirname( $webcamBasePath ) ); // Get realpath of target directory 
	    if ( strpos( $webcamBaseRootDir, $webcamContentRootDir ) !== 0 ) // The uploaded file's path is different from the content path?    
	    {
			VidiunLog::err( "webcamTokenId [$webcamTokenId] points outside of webcam content directory" );
	    	throw new VidiunAPIException( VidiunErrors::INVALID_WEBCAM_TOKEN_ID );
	    }
	     
		if (!file_exists("$webcamBasePath.flv") && !file_exists("$webcamBasePath.f4v") && !file_exists("$webcamBasePath.f4v.mp4"))
		{
			if (vDataCenterMgr::dcExists(1 - vDataCenterMgr::getCurrentDcId()))
				vFileUtils::dumpApiRequest ( vDataCenterMgr::getRemoteDcExternalUrlByDcId ( 1 - vDataCenterMgr::getCurrentDcId () ) );
			throw new VidiunAPIException ( VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND );
		}

		$dbEntry = $this->prepareEntryForInsert($mediaEntry);

        $vshowId = $dbEntry->getVshowId();

		// setup the needed params for my insert entry helper
		$paramsArray = array (
			"entry_media_source" => VidiunSourceType::WEBCAM,
            "entry_media_type" => $dbEntry->getMediaType(),
			"webcam_suffix" => $webcamTokenId,
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

		myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry);

		$mediaEntry->fromObject($dbEntry, $this->getResponseProfile());
		return $mediaEntry;
	}

	/**
	 * Copy entry into new entry
	 *
	 * @action addFromEntry
	 * @param string $sourceEntryId Media entry id to copy from
	 * @param VidiunMediaEntry $mediaEntry Media entry metadata
	 * @param int $sourceFlavorParamsId The flavor to be used as the new entry source, source flavor will be used if not specified
	 * @return VidiunMediaEntry The new media entry
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::ORIGINAL_FLAVOR_ASSET_IS_MISSING
	 * @throws VidiunErrors::FLAVOR_PARAMS_NOT_FOUND
	 * @throws VidiunErrors::ORIGINAL_FLAVOR_ASSET_NOT_CREATED
	 *
	 * @deprecated use media.add instead
	 */
	function addFromEntryAction($sourceEntryId, VidiunMediaEntry $mediaEntry = null, $sourceFlavorParamsId = null)
	{
    	if($mediaEntry->conversionQuality && !$mediaEntry->conversionProfileId)
    		$mediaEntry->conversionProfileId = $mediaEntry->conversionQuality;

		$srcEntry = entryPeer::retrieveByPK($sourceEntryId);

		if (!$srcEntry || $srcEntry->getType() != entryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $sourceEntryId);

		$srcFlavorAsset = null;
		if(is_null($sourceFlavorParamsId))
		{
			$srcFlavorAsset = assetPeer::retrieveOriginalByEntryId($sourceEntryId);
			if(!$srcFlavorAsset)
				throw new VidiunAPIException(VidiunErrors::ORIGINAL_FLAVOR_ASSET_IS_MISSING);
		}
		else
		{
			$srcFlavorAssets = assetPeer::retrieveReadyByEntryIdAndFlavorParams($sourceEntryId, array($sourceFlavorParamsId));
			if(count($srcFlavorAssets))
			{
				$srcFlavorAsset = reset($srcFlavorAssets);
			}
			else
			{
				throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_NOT_FOUND);
			}
		}

		if ($mediaEntry === null)
			$mediaEntry = new VidiunMediaEntry();

		$mediaEntry->mediaType = $srcEntry->getMediaType();

		return $this->addEntryFromFlavorAsset($mediaEntry, $srcEntry, $srcFlavorAsset);
	}

	/**
	 * Copy flavor asset into new entry
	 *
	 * @action addFromFlavorAsset
	 * @param string $sourceFlavorAssetId Flavor asset id to be used as the new entry source
	 * @param VidiunMediaEntry $mediaEntry Media entry metadata
	 * @return VidiunMediaEntry The new media entry
	 * @throws VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::ORIGINAL_FLAVOR_ASSET_NOT_CREATED
	 *
	 * @deprecated use media.add instead
	 */
	function addFromFlavorAssetAction($sourceFlavorAssetId, VidiunMediaEntry $mediaEntry = null)
	{
    	if($mediaEntry->conversionQuality && !$mediaEntry->conversionProfileId)
    		$mediaEntry->conversionProfileId = $mediaEntry->conversionQuality;

		$srcFlavorAsset = assetPeer::retrieveById($sourceFlavorAssetId);

		if (!$srcFlavorAsset)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $sourceFlavorAssetId);

		$sourceEntryId = $srcFlavorAsset->getEntryId();
		$srcEntry = entryPeer::retrieveByPK($sourceEntryId);

		if (!$srcEntry || $srcEntry->getType() != entryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $sourceEntryId);

		if ($mediaEntry === null)
			$mediaEntry = new VidiunMediaEntry();

		$mediaEntry->mediaType = $srcEntry->getMediaType();

		return $this->addEntryFromFlavorAsset($mediaEntry, $srcEntry, $srcFlavorAsset);
	}

	/**
	 * Convert entry
	 *
	 * @action convert
	 * @param string $entryId Media entry id
	 * @param int $conversionProfileId
	 * @param VidiunConversionAttributeArray $dynamicConversionAttributes
	 * @return bigint job id
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::FLAVOR_PARAMS_NOT_FOUND
	 */
	function convertAction($entryId, $conversionProfileId = null, VidiunConversionAttributeArray $dynamicConversionAttributes = null)
	{
		return $this->convert($entryId, $conversionProfileId, $dynamicConversionAttributes);
	}

	/**
	 * Get media entry by ID.
	 *
	 * @action get
	 * @param string $entryId Media entry id
	 * @param int $version Desired version of the data
	 * @return VidiunMediaEntry The requested media entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function getAction($entryId, $version = -1)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry || !(VidiunEntryFactory::getInstanceByType($dbEntry->getType()) instanceof VidiunMediaEntry))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		return $this->getEntry($entryId, $version);
	}

    /**
     * Get MRSS by entry id
     * XML will return as an escaped string
     *
     * @action getMrss
     * @param string $entryId Entry id
     * @param VidiunExtendingItemMrssParameterArray $extendingItemsArray
     * @param string $features
     * @return string
     * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
     */
    function getMrssAction($entryId, VidiunExtendingItemMrssParameterArray $extendingItemsArray = null, $features = null)
    {
        $dbEntry = entryPeer::retrieveByPKNoFilter($entryId);
		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		$mrssParams = new vMrssParameters();
		if ($extendingItemsArray)
		{
			$coreExtendingItemArray = $extendingItemsArray->toObjectsArray();
			$mrssParams->setItemXpathsToExtend($coreExtendingItemArray);
		}
        /* @var $mrss SimpleXMLElement */
        $mrss = vMrssManager::getEntryMrssXml($dbEntry, null, $mrssParams, ($features ? explode(',', $features) : null));
        return $mrss->asXML();
    }

	/**
	 * Update media entry. Only the properties that were set will be updated.
	 *
	 * @action update
	 * @param string $entryId Media entry id to update
	 * @param VidiunMediaEntry $mediaEntry Media entry metadata to update
	 * @return VidiunMediaEntry The updated media entry
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry entryId edit
	 */
	function updateAction($entryId, VidiunMediaEntry $mediaEntry)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
		{
			$dcIndex = vDataCenterMgr::getDCByObjectId($entryId, true);
			if ($dcIndex != vDataCenterMgr::getCurrentDcId())
			{
				VidiunLog::info("EntryID [$entryId] wasn't found on current DC. dumping the request to DC id [$dcIndex]");
				vFileUtils::dumpApiRequest ( vDataCenterMgr::getRemoteDcExternalUrlByDcId ($dcIndex ), true );
			}
		}
		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$mediaEntry = $this->updateEntry($entryId, $mediaEntry, VidiunEntryType::MEDIA_CLIP);

		return $mediaEntry;
	}

	/**
	 * Replace content associated with the media entry.
	 *
	 * @action updateContent
	 * @param string $entryId Media entry id to update
	 * @param VidiunResource $resource Resource to be used to replace entry media content
	 * @param int $conversionProfileId The conversion profile id to be used on the entry
	 * @param VidiunEntryReplacementOptions $advancedOptions Additional update content options
	 * @return VidiunMediaEntry The updated media entry
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_REPLACEMENT_ALREADY_EXISTS
     * @throws VidiunErrors::INVALID_OBJECT_ID
     * @validateUser entry entryId edit
	 */
	function updateContentAction($entryId, VidiunResource $resource, $conversionProfileId = null, $advancedOptions = null)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		//calling replaceResource only if no lock or we grabbed it
		$lock = vLock::create("media_updateContent_{$entryId}");
		
		if ($lock && !$lock->lock(self::VLOCK_MEDIA_UPDATECONTENT_GRAB_TIMEOUT , self::VLOCK_MEDIA_UPDATECONTENT_HOLD_TIMEOUT))
			throw new VidiunAPIException(VidiunErrors::ENTRY_REPLACEMENT_ALREADY_EXISTS);
		
		try{
			$this->replaceResource($resource, $dbEntry, $conversionProfileId, $advancedOptions);
			$this->validateContent($dbEntry);
			if ($this->shouldUpdateRelatedEntry($resource))
				$this->updateContentInRelatedEntries($resource, $dbEntry, $conversionProfileId, $advancedOptions);
		}
		catch(Exception $e){
			if($lock){
				$lock->unlock();
			}
			$this->handleErrorDuringSetResource($entryId, $e);
		}
		if($lock){
			$lock->unlock();
		}

		return $this->getEntry($entryId);
	}

	/**
	 * Delete a media entry.
	 *
	 * @action delete
	 * @param string $entryId Media entry id to delete
	 *
 	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
 	 * @validateUser entry entryId edit
	 */
	function deleteAction($entryId)
	{
		$this->deleteEntry($entryId, VidiunEntryType::MEDIA_CLIP);
	}

	/**
	 * Approves media replacement
	 *
	 * @action approveReplace
	 * @param string $entryId Media entry id to replace
	 * @return VidiunMediaEntry The replaced media entry
	 *
 	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry entryId edit
	 */
	function approveReplaceAction($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		$this->validateEntryForReplace($entryId, $dbEntry, VidiunEntryType::MEDIA_CLIP);
		$this->approveReplace($dbEntry);

		$childEntries = entryPeer::retrieveChildEntriesByEntryIdAndPartnerId($entryId, $dbEntry->getPartnerId());
		foreach ($childEntries as $childEntry)
		{
			if ($childEntry->getId() != $entryId)
			{
				$this->validateEntryForReplace($childEntry->getId(), $childEntry);
				$this->approveReplace($childEntry);
			}
		}

		return $this->getEntry($entryId, -1, VidiunEntryType::MEDIA_CLIP);
	}

	/**
	 * Cancels media replacement
	 *
	 * @action cancelReplace
	 * @param string $entryId Media entry id to cancel
	 * @return VidiunMediaEntry The canceled media entry
	 *
 	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry entryId edit
	 */
	function cancelReplaceAction($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		$this->validateEntryForReplace($entryId, $dbEntry, VidiunEntryType::MEDIA_CLIP);
		$this->cancelReplace($dbEntry);

		$childEntries = entryPeer::retrieveChildEntriesByEntryIdAndPartnerId($entryId, $dbEntry->getPartnerId());
		foreach ($childEntries as $childEntry)
		{
			if ($childEntry->getId() != $entryId)
			{
				$this->validateEntryForReplace($childEntry->getId(), $childEntry);
				$this->cancelReplace($childEntry);
			}
		}

		return $this->getEntry($entryId, -1, VidiunEntryType::MEDIA_CLIP);
	}

	/**
	* List media entries by filter with paging support.
	*
	* @action list
	* @param VidiunMediaEntryFilter $filter Media entry filter
	* @param VidiunFilterPager $pager Pager
	* @return VidiunMediaListResponse Wrapper for array of media entries and total count
	*/
	function listAction(VidiunMediaEntryFilter $filter = null, VidiunFilterPager $pager = null)
	{
	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;

	    if (!$filter)
			$filter = new VidiunMediaEntryFilter();
	
	    list($list, $totalCount) = parent::listEntriesByFilter($filter, $pager);

	    $newList = VidiunMediaEntryArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunMediaListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}

	/**
	* Count media entries by filter.
	*
	* @action count
	* @param VidiunMediaEntryFilter $filter Media entry filter
	* @return int
	*/
	function countAction(VidiunMediaEntryFilter $filter = null)
	{
	    if (!$filter)
			$filter = new VidiunMediaEntryFilter();

		$filter->typeEqual = VidiunEntryType::MEDIA_CLIP;

		return parent::countEntriesByFilter($filter);
	}

	/**
	 * Upload a media file to Vidiun, then the file can be used to create a media entry.
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

		$res = myUploadUtils::uploadFileByToken($fileData, $token, "", null, true);

		return $res["token"];
	}

	/**
	 * Update media entry thumbnail by a specified time offset (In seconds)
	 * If flavor params id not specified, source flavor will be used by default
	 *
	 * @action updateThumbnail
	 * @param string $entryId Media entry id
	 * @param int $timeOffset Time offset (in seconds)
	 * @param int $flavorParamsId The flavor params id to be used
	 * @return VidiunMediaEntry The media entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 *
	 * @deprecated
	 */
	function updateThumbnailAction($entryId, $timeOffset, $flavorParamsId = null)
	{
		return parent::updateThumbnailForEntryFromSourceEntry($entryId, $entryId, $timeOffset, VidiunEntryType::MEDIA_CLIP, $flavorParamsId);
	}

	/**
	 * Update media entry thumbnail from a different entry by a specified time offset (In seconds)
	 * If flavor params id not specified, source flavor will be used by default
	 *
	 * @action updateThumbnailFromSourceEntry
	 * @param string $entryId Media entry id
	 * @param string $sourceEntryId Media entry id
	 * @param int $timeOffset Time offset (in seconds)
	 * @param int $flavorParamsId The flavor params id to be used
	 * @return VidiunMediaEntry The media entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 *
	 * @deprecated
	 */
	function updateThumbnailFromSourceEntryAction($entryId, $sourceEntryId, $timeOffset, $flavorParamsId = null)
	{
		return parent::updateThumbnailForEntryFromSourceEntry($entryId, $sourceEntryId, $timeOffset, VidiunEntryType::MEDIA_CLIP, $flavorParamsId);
	}

	/**
	 * Update media entry thumbnail using a raw jpeg file
	 *
	 * @action updateThumbnailJpeg
	 * @param string $entryId Media entry id
	 * @param file $fileData Jpeg file data
	 * @return VidiunMediaEntry The media entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 *
	 * @deprecated
	 */
	function updateThumbnailJpegAction($entryId, $fileData)
	{
		return parent::updateThumbnailJpegForEntry($entryId, $fileData, VidiunEntryType::MEDIA_CLIP);
	}

	/**
	 * Update entry thumbnail using URL
	 *
	 * @action updateThumbnailFromUrl
	 * @param string $entryId Media entry id
	 * @param string $url file url
	 * @return VidiunBaseEntry The media entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 *
	 * @deprecated
	 */
	function updateThumbnailFromUrlAction($entryId, $url)
	{
		return parent::updateThumbnailForEntryFromUrl($entryId, $url, VidiunEntryType::MEDIA_CLIP);
	}

	/**
	 * Request a new conversion job, this can be used to convert the media entry to a different format
	 *
	 * @action requestConversion
	 * @param string $entryId Media entry id
	 * @param string $fileFormat Format to convert
	 * @return int The queued job id
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function requestConversionAction($entryId, $fileFormat)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ($dbEntry->getMediaType() == VidiunMediaType::AUDIO)
		{
			// for audio - force format flv regardless what the user really asked for
			$fileFormat = "flv";
		}

//		$job = myBatchDownloadVideoServer::addJob($this->getVuser()->getPuserId(), $dbEntry, null, $fileFormat);
		$flavorParams = myConversionProfileUtils::getFlavorParamsFromFileFormat ( $this->getPartnerId() , $fileFormat );

		$err = null;
		$job = vBusinessPreConvertDL::decideAddEntryFlavor(null, $dbEntry->getId(), $flavorParams->getId(), $err);

		if ( $job )
			return $job->getId();
		else
			return null;
	}

	/**
	 * Flag inappropriate media entry for moderation
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
		return parent::flagEntry($moderationFlag, VidiunEntryType::MEDIA_CLIP);
	}

	/**
	 * Reject the media entry and mark the pending flags (if any) as moderated (this will make the entry non playable)
	 *
	 * @action reject
	 * @param string $entryId
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function rejectAction($entryId)
	{
		parent::rejectEntry($entryId, VidiunEntryType::MEDIA_CLIP);
	}

	/**
	 * Approve the media entry and mark the pending flags (if any) as moderated (this will make the entry playable)
	 *
	 * @action approve
	 * @param string $entryId
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function approveAction($entryId)
	{
		parent::approveEntry($entryId, VidiunEntryType::MEDIA_CLIP);
	}

	/**
	 * List all pending flags for the media entry
	 *
	 * @action listFlags
	 * @param string $entryId
	 * @param VidiunFilterPager $pager
	 * @return VidiunModerationFlagListResponse
	 */
	public function listFlags($entryId, VidiunFilterPager $pager = null)
	{
		return parent::listFlagsForEntry($entryId, $pager);
	}

	/**
	 * Anonymously rank a media entry, no validation is done on duplicate rankings
	 *
	 * @action anonymousRank
	 * @param string $entryId
	 * @param int $rank
	 */
	public function anonymousRankAction($entryId, $rank)
	{
		return parent::anonymousRankEntry($entryId, VidiunEntryType::MEDIA_CLIP, $rank);
	}

	/* (non-PHPdoc)
	 * @see VidiunEntryService::prepareEntryForInsert()
	 */
	protected function prepareEntryForInsert(VidiunBaseEntry $entry, entry $dbEntry = null)
	{
		if(!($entry instanceof VidiunMediaEntry))
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_TYPE,$entry->id, $entry->getType(), entryType::MEDIA_CLIP);
		$entry->validatePropertyNotNull("mediaType");

		$conversionQuality = $this->getConversionQuality($entry);
		if (!is_null($conversionQuality)) {
			$entry->conversionQuality = $conversionQuality;
			if (!$entry->conversionProfileId) {
				$entry->conversionProfileId = $entry->conversionQuality;
			}
		}

		if ($dbEntry == null){
			$dbEntry = $this->duplicateTemplateEntry($entry->conversionProfileId, $entry->templateEntryId);
		}

		$dbEntry = parent::prepareEntryForInsert($entry, $dbEntry);

		$vshow = $this->createDummyVShow();
	        $vshowId = $vshow->getId();
		$dbEntry->setVshowId($vshowId);
		$dbEntry->save();
		return $dbEntry;
	}

	private function getConversionQuality($entry)
	{
		$conversionQuality = $entry->conversionQuality;
		if (parent::getConversionQualityFromRequest())
			$conversionQuality = parent::getConversionQualityFromRequest();
		if(is_null($conversionQuality))
			return null;
		$conversionProfile2 = conversionProfile2Peer::retrieveByPK($conversionQuality);
		if (!$conversionProfile2) {
			$conversionProfile = ConversionProfilePeer::retrieveByPK($conversionQuality);
			if ($conversionProfile)
				$conversionQuality = $conversionProfile->getConversionProfile2Id();
		}
		return $conversionQuality;
	}

	/**
	 * @param $vResource
	 * @return bool
	 */
	protected function isResourceVClip($vResource)
	{
		/**
		 * @var vOperationResource $vResource
		 */
		foreach ($vResource->getOperationAttributes() as $opAttribute)
		{
			if ($opAttribute instanceof vClipAttributes)
			{
				return true;
			}
		}
		return false;
	}
	
	private static function getRelatedResourceEntryId($originalResourceEntryId,$dbEntry,$relatedEntry)
	{
		if($originalResourceEntryId == $dbEntry->getSourceEntryId() &&  $relatedEntry->getSourceEntryId() )
		{
			return $relatedEntry->getSourceEntryId();
		}
		if($originalResourceEntryId == $dbEntry->getRootEntryId())
		{
			return $relatedEntry->getRootEntryId();
		}
		
		return $relatedEntry->getId();
	}
	
	
	private function updateContentInRelatedEntries($resource, $dbEntry, $conversionProfileId, $advancedOptions)
	{
		$originalResourceEntryId = $resource->resource->entryId;
		$relatedEntries = myEntryUtils::getRelatedEntries($dbEntry);
		
		foreach ($relatedEntries as $relatedEntry)
		{
			if ($relatedEntry->getType() == entryType::DOCUMENT)
				continue;
			$resource->resource->entryId = self::getRelatedResourceEntryId($originalResourceEntryId, $dbEntry, $relatedEntry);
			VidiunLog::debug("Replacing entry [" . $relatedEntry->getId() . "] as related entry with resource entry id : [" . $resource->resource->entryId . "]");
			$this->replaceResource($resource, $relatedEntry, $conversionProfileId, $advancedOptions);
		}
	}
	
	private function shouldUpdateRelatedEntry($resource)
	{
		return $this->isClipTrimFlow($resource);
	}

	private function isClipTrimFlow($resource)
	{
		return ($resource instanceof VidiunOperationResource && $resource->resource instanceof VidiunEntryResource
			&& $resource->operationAttributes[0] instanceof VidiunClipAttributes);
	}

	/**
	 * Get volume map by entry id
	 *
	 * @action getVolumeMap
	 * @param string $entryId Entry id
	 * @return file
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function getVolumeMapAction($entryId)
	{
		$dbEntry = entryPeer::retrieveByPKNoFilter($entryId);
		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$flavorAsset = myEntryUtils::getFlavorSupportedByPackagerForVolumeMap($entryId);
		if (!$flavorAsset)
			throw new VidiunAPIException(VidiunErrors::GIVEN_ID_NOT_SUPPORTED);

		$content = myEntryUtils::getVolumeMapContent($flavorAsset);
		return $content;
	}

	private function handleErrorDuringSetResource($entryId, Exception $e)
	{
		if ($e->getCode() == APIErrors::getCode(APIErrors::ENTRY_ID_NOT_FOUND))
		{
			throw $e; //if no entry found then no need to do anything
		}
		VidiunLog::info("Exception was thrown during setContent on entry [$entryId] with error: " . $e->getMessage());
		$this->cancelReplaceAction($entryId);

		$errorCodeArr = array(vCoreException::SOURCE_FILE_NOT_FOUND, APIErrors::getCode(APIErrors::SOURCE_FILE_NOT_FOUND));
		if ((in_array($e->getCode(), $errorCodeArr)) && (vDataCenterMgr::dcExists(1 - vDataCenterMgr::getCurrentDcId())))
		{
			$remoteDc = 1 - vDataCenterMgr::getCurrentDcId();
			VidiunLog::info("Source file wasn't found on current DC. Dumping the request to DC ID [$remoteDc]");
			vFileUtils::dumpApiRequest(vDataCenterMgr::getRemoteDcExternalUrlByDcId($remoteDc), true);
		}
		throw $e;
	}
}

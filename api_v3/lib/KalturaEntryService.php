<?php
/**
 * @package api
 * @subpackage services
 */
class VidiunEntryService extends VidiunBaseService 
{
	
	  //amount of time for attempting to grab vLock
	  const VLOCK_MEDIA_UPDATECONTENT_GRAB_TIMEOUT = 0.1;
	
	  //amount of time for holding vLock
	  const VLOCK_MEDIA_UPDATECONTENT_HOLD_TIMEOUT = 7;

	public function initService($serviceId, $serviceName, $actionName)
	{
		$vs = vCurrentContext::$vs_object ? vCurrentContext::$vs_object : null;
		
		if (($actionName == 'list' || $actionName == 'count' || $actionName == 'listByReferenceId') &&
		  (!$vs || (!$vs->isAdmin() && !$vs->verifyPrivileges(vs::PRIVILEGE_LIST, vs::PRIVILEGE_WILDCARD))))
		{			
			VidiunCriterion::enableTag(VidiunCriterion::TAG_WIDGET_SESSION);
			entryPeer::setUserContentOnly(true);
		}
		
		
/*		//to support list categories with entitlmenet for user that is a member of more then 100 large categories
 		//large category is a category with > 10 members or > 100 entries. 				
  		if ($actionName == 'list' && vEntitlementUtils::getEntitlementEnforcement())
		{
			$dispatcher = VidiunDispatcher::getInstance();
			$arguments = $dispatcher->getArguments();
			
			$categoriesIds = array();
			$categories = array();
			foreach($arguments as $argument)
			{
				if ($argument instanceof VidiunBaseEntryFilter)
				{
					if(isset($argument->categoriesMatchAnd))
						$categories = array_merge($categories, explode(',', $argument->categoriesMatchAnd));
						
					if(isset($argument->categoriesMatchOr))
						$categories = array_merge($categories, explode(',', $argument->categoriesMatchOr));
					
					if(isset($argument->categoriesFullNameIn))
						$categories = array_merge($categories, explode(',', $argument->categoriesFullNameIn));
						
					if(count($categories))
					{
						$categories = categoryPeer::getByFullNamesExactMatch($categories);
						
						foreach ($categories as $category)
							$categoriesIds[] = $category->getId();
					}
										
					if(isset($argument->categoriesIdsMatchAnd))
						$categoriesIds = array_merge($categoriesIds, explode(',', $argument->categoriesIdsMatchAnd));
					
					if(isset($argument->categoriesIdsMatchOr))
						$categoriesIds = array_merge($categoriesIds, explode(',', $argument->categoriesIdsMatchOr));
					
					if(isset($argument->categoryAncestorIdIn))
						$categoriesIds = array_merge($categoriesIds, explode(',', $argument->categoryAncestorIdIn));
				}
			}
			
			foreach($categoriesIds as $key => $categoryId)
			{
				if(!$categoryId)
				{
					unset($categoriesIds[$key]);
				}
			}
			
			if(count($categoriesIds))
				entryPeer::setFilterdCategoriesIds($categoriesIds);
		}*/
		
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('ConversionProfile');
		$this->applyPartnerFilterForClass('conversionProfile2');
	}
	
	/**
	 * @param vResource $resource
	 * @param entry $dbEntry
	 * @param asset $asset
	 * @return asset
	 * @throws VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED
	 */
	protected function attachResource(vResource $resource, entry $dbEntry, asset $asset = null)
	{
		throw new VidiunAPIException(VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED, $dbEntry->getType());
	}
	
	/**
	 * @param VidiunResource $resource
	 * @param entry $dbEntry
	 */
	protected function replaceResource(VidiunResource $resource, entry $dbEntry)
	{
		throw new VidiunAPIException(VidiunErrors::ENTRY_TYPE_NOT_SUPPORTED, $dbEntry->getType());
	}
	
	/**
	 * General code that replaces given entry resource with a given resource, and mark the original
	 * entry as replaced
	 * @param VidiunEntry $dbEntry The original entry we'd like to replace
	 * @param VidiunResource $resource The resource we'd like to attach
	 * @param VidiunEntry $tempMediaEntry The replacing entry
	 * @throws VidiunAPIException
	 */
	protected function replaceResourceByEntry($dbEntry, $resource, $tempMediaEntry) 
	{
		$partner = $this->getPartner();
		if(!$partner->getEnabledService(PermissionName::FEATURE_ENTRY_REPLACEMENT))
		{
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, PermissionName::FEATURE_ENTRY_REPLACEMENT);
		}
		
		if($dbEntry->getReplacingEntryId())
			throw new VidiunAPIException(VidiunErrors::ENTRY_REPLACEMENT_ALREADY_EXISTS);
		
		$resource->validateEntry($dbEntry);
		
		// create the temp db entry first and mark it as isTemporary == true
		$entryType = vPluginableEnumsManager::apiToCore('entryType', $tempMediaEntry->type);
		$class = entryPeer::getEntryClassByType($entryType);
			
		VidiunLog::debug("Creating new entry of API type [{$tempMediaEntry->type}] core type [$entryType] class [$class]");
		$tempDbEntry = new $class();
		$tempDbEntry->setIsTemporary(true);
		$tempDbEntry->setDisplayInSearch(mySearchUtils::DISPLAY_IN_SEARCH_SYSTEM);
		$tempDbEntry->setReplacedEntryId($dbEntry->getId());

		$vResource = $resource->toObject();
		if ($vResource->getType() == 'vOperationResource')
			$tempDbEntry->setTempTrimEntry(true);

		$tempDbEntry = $this->prepareEntryForInsert($tempMediaEntry, $tempDbEntry);
		$tempDbEntry->setPartnerId($dbEntry->getPartnerId());
		$tempDbEntry->save();
		
		$dbEntry->setReplacingEntryId($tempDbEntry->getId());
		$dbEntry->setReplacementStatus(entryReplacementStatus::NOT_READY_AND_NOT_APPROVED);
		if(!$partner->getEnabledService(PermissionName::FEATURE_ENTRY_REPLACEMENT_APPROVAL) || $dbEntry->getSourceType() == EntrySourceType::VIDIUN_RECORDED_LIVE)
			$dbEntry->setReplacementStatus(entryReplacementStatus::APPROVED_BUT_NOT_READY);
		$dbEntry->save();

		$this->attachResource($vResource, $tempDbEntry);
	}

	protected function validateEntryForReplace($entryId, $dbEntry, $entryType = null)
	{
		if (!$dbEntry)
		{
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		}

		if ($entryType && $dbEntry->getType() != $entryType)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_TYPE, $entryId, $dbEntry->getType(), $entryType);
		}
	}

	public function isApproveReplaceRequired($dbEntry)
	{
		if ($dbEntry->getMediaType() == KalturaMediaType::IMAGE)
		{
			return false;
		}
		return true;
	}

	/**
	 * Approves entry replacement
	 *
	 * @param $dbEntry
	 * @throws VidiunAPIException
	 */
	protected function approveReplace($dbEntry)
	{
		if (!$this->isApproveReplaceRequired($dbEntry))
		{
			return;
		}

		switch ($dbEntry->getReplacementStatus())
		{
			case entryReplacementStatus::APPROVED_BUT_NOT_READY:
				break;

			case entryReplacementStatus::READY_BUT_NOT_APPROVED:
				vBusinessConvertDL::replaceEntry($dbEntry);
				break;

			case entryReplacementStatus::NOT_READY_AND_NOT_APPROVED:
				$dbEntry->setReplacementStatus(entryReplacementStatus::APPROVED_BUT_NOT_READY);
				$dbEntry->save();

				//preventing race conditions of temp entry being ready just as you approve the replacement
				$dbReplacingEntry = entryPeer::retrieveByPK($dbEntry->getReplacingEntryId());
				if ($dbReplacingEntry && $dbReplacingEntry->getStatus() == entryStatus::READY)
					vBusinessConvertDL::replaceEntry($dbEntry);
				break;

			case entryReplacementStatus::NONE:
			case entryReplacementStatus::FAILED:
			default:
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_REPLACED, $dbEntry->getId());
				break;
		}
	}

	/**
	 * Cancels media replacement
	 * 
	 * @param $dbEntry
	 * @throws VidiunAPIException
	 */
	protected function cancelReplace($dbEntry)
	{
		if ($dbEntry->getReplacingEntryId())
		{
			$dbTempEntry = entryPeer::retrieveByPK($dbEntry->getReplacingEntryId());
			if ($dbTempEntry)
			{
				myEntryUtils::deleteEntry($dbTempEntry);
			}
		}

		$dbEntry->setReplacingEntryId(null);
		$dbEntry->setReplacementStatus(entryReplacementStatus::NONE);
		$dbEntry->save();
	}

	/**
	 * @param vFileSyncResource $resource
	 * @param entry $dbEntry
	 * @param asset $dbAsset
	 * @return asset | NULL in case of IMAGE entry
	 * @throws VidiunErrors::UPLOAD_ERROR
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	protected function attachFileSyncResource(vFileSyncResource $resource, entry $dbEntry, asset $dbAsset = null)
	{
		$dbEntry->setSource(entry::ENTRY_MEDIA_SOURCE_VIDIUN);
		$dbEntry->save();
		
		try{
			$syncable = vFileSyncObjectManager::retrieveObject($resource->getFileSyncObjectType(), $resource->getObjectId());
		}
		catch(vFileSyncException $e){
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $resource->getObjectId());
		}
		
		$srcSyncKey = $syncable->getSyncKey($resource->getObjectSubType(), $resource->getVersion());
		$encryptionKey = method_exists($syncable, 'getEncryptionKey') ? $syncable->getEncryptionKey() : null;
		$dbAsset = $this->attachFileSync($srcSyncKey, $dbEntry, $dbAsset, $encryptionKey);
		
		//In case the target entry's media type is image no asset is created and the image is set on a entry level file sync
		if(!$dbAsset && $dbEntry->getMediaType() == VidiunMediaType::IMAGE)
			return null;
		
		// Copy the media info from the old asset to the new one
		if($syncable instanceof asset && $resource->getObjectSubType() == asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET)
		{
			$mediaInfo = mediaInfoPeer::retrieveByFlavorAssetId($syncable->getId());
			if($mediaInfo)
			{
				$newMediaInfo = $mediaInfo->copy();
				$newMediaInfo->setFlavorAssetId($dbAsset->getId());
				$newMediaInfo->save();
			}
			
			if ($dbAsset->getStatus() == asset::ASSET_STATUS_READY)
			{
				$dbEntry->syncFlavorParamsIds();
				$dbEntry->save();
			}
		}
		
		return $dbAsset;
	}

	/**
	 * @param vLiveEntryResource $resource
	 * @param entry $dbEntry
	 * @param asset $dbAsset
	 * @return array $operationAttributes
	 * @return asset
	 */
	protected function attachLiveEntryResource(vLiveEntryResource $resource, entry $dbEntry, asset $dbAsset = null, array $operationAttributes = null)
	{
		$dbEntry->setRootEntryId($resource->getEntry()->getId());
		$dbEntry->setSource(EntrySourceType::RECORDED_LIVE);
		if ($operationAttributes)
			$dbEntry->setOperationAttributes($operationAttributes);
		$dbEntry->save();
	
		if(!$dbAsset)
		{
			$dbAsset = vFlowHelper::createOriginalFlavorAsset($this->getPartnerId(), $dbEntry->getId());
		}
		
		$offset = null;
		$duration = null;
		$requiredDuration = null;
		$clipAttributes = null;
		if(is_array($operationAttributes))
		{
			foreach($operationAttributes as $operationAttributesItem)
			{
				if($operationAttributesItem instanceof vClipAttributes)
				{
					$clipAttributes = $operationAttributesItem;
					
					// convert milliseconds to seconds
					$offset = $operationAttributesItem->getOffset();
					$duration = $operationAttributesItem->getDuration();
					$requiredDuration = $offset + $duration;
				}
			}
		}
		
		$dbLiveEntry = $resource->getEntry();
		$dbRecordedEntry = entryPeer::retrieveByPK($dbLiveEntry->getRecordedEntryId());
		
		if(!$dbRecordedEntry || ($requiredDuration && $requiredDuration > $dbRecordedEntry->getLengthInMsecs()))
		{
			$mediaServer = $dbLiveEntry->getMediaServer(true);
			if(!$mediaServer)
				throw new VidiunAPIException(VidiunErrors::NO_MEDIA_SERVER_FOUND, $dbLiveEntry->getId());
				
			$mediaServerLiveService = $mediaServer->getWebService($mediaServer->getLiveWebServiceName());
			if($mediaServerLiveService && $mediaServerLiveService instanceof VidiunMediaServerLiveService)
			{
				$mediaServerLiveService->splitRecordingNow($dbLiveEntry->getId());
				$dbLiveEntry->attachPendingMediaEntry($dbEntry, $requiredDuration, $offset, $duration);
				$dbLiveEntry->save();
			}
			else 
			{
				throw new VidiunAPIException(VidiunErrors::MEDIA_SERVER_SERVICE_NOT_FOUND, $mediaServer->getId(), $mediaServer->getLiveWebServiceName());
			}
			return $dbAsset;
		}
		
		$dbRecordedAsset = assetPeer::retrieveOriginalReadyByEntryId($dbRecordedEntry->getId());
		if(!$dbRecordedAsset)
		{
			$dbRecordedAssets = assetPeer::retrieveReadyFlavorsByEntryId($dbRecordedEntry->getId());
			$dbRecordedAsset = array_pop($dbRecordedAssets);
		}
		/* @var $dbRecordedAsset flavorAsset */
		
		$isNewAsset = false;
		if(!$dbAsset)
		{
			$isNewAsset = true;
			$dbAsset = vFlowHelper::createOriginalFlavorAsset($this->getPartnerId(), $dbEntry->getId());
		}
		
		if(!$dbAsset && $dbEntry->getStatus() == entryStatus::NO_CONTENT)
		{
			$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
			$dbEntry->save();
		}
		
		$sourceSyncKey = $dbRecordedAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		
		$dbAsset->setFileExt($dbRecordedAsset->getFileExt());
		$dbAsset->save();
		
		$syncKey = $dbAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		
		try {
			vFileSyncUtils::createSyncFileLinkForKey($syncKey, $sourceSyncKey);
		}
		catch (Exception $e) {
			
			if($dbEntry->getStatus() == entryStatus::NO_CONTENT)
			{
				$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
				$dbEntry->save();
			}

			$dbAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
			$dbAsset->save();												
			throw $e;
		}
		

		if($requiredDuration)
		{
			$errDescription = '';
 			vBusinessPreConvertDL::decideAddEntryFlavor(null, $dbEntry->getId(), $clipAttributes->getAssetParamsId(), $errDescription, $dbAsset->getId(), array($clipAttributes));
		}
		else
		{
			if($isNewAsset)
				vEventsManager::raiseEvent(new vObjectAddedEvent($dbAsset));
		}
		vEventsManager::raiseEvent(new vObjectDataChangedEvent($dbAsset));
			
		return $dbAsset;
	}
	
	/**
	 * @param vLocalFileResource $resource
	 * @param entry $dbEntry
	 * @param asset $dbAsset
	 * @return asset
	 */
	protected function attachLocalFileResource(vLocalFileResource $resource, entry $dbEntry, asset $dbAsset = null)
	{
		$dbEntry->setSource($resource->getSourceType());
		$dbEntry->save();

		if ($resource->getIsReady())
		{
			return $this->attachFile($resource->getLocalFilePath(), $dbEntry, $dbAsset, $resource->getKeepOriginalFile());
		}
	
		$lowerStatuses = array(
			entryStatus::ERROR_CONVERTING,
			entryStatus::ERROR_IMPORTING,
			entryStatus::PENDING,
			entryStatus::NO_CONTENT,
		);
		
		$entryUpdated = false;
		if(in_array($dbEntry->getStatus(), $lowerStatuses))
		{
			$dbEntry->setStatus(entryStatus::IMPORT);
			$entryUpdated = true;
		}
		
		if($dbEntry->getMediaType() == null && $dbEntry->getType() == entryType::MEDIA_CLIP)
		{
			$mediaType = $resource->getMediaType();
			if($mediaType)
			{
				$dbEntry->setMediaType($mediaType);
				$entryUpdated = true;
			}
		}
		
		if($entryUpdated)
			$dbEntry->save();
		
		// TODO - move image handling to media service
		if($dbEntry->getMediaType() == VidiunMediaType::IMAGE)
		{
			$resource->attachCreatedObject($dbEntry);
			return null;
		}
		
		$isNewAsset = false;
		if(!$dbAsset)
		{
			$isNewAsset = true;
			$dbAsset = vFlowHelper::createOriginalFlavorAsset($this->getPartnerId(), $dbEntry->getId());
		}
		
		if(!$dbAsset)
		{
			if($dbEntry->getStatus() == entryStatus::NO_CONTENT)
			{
				$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
				$dbEntry->save();
			}
			
			return null;
		}
		
		$dbAsset->setStatus(asset::FLAVOR_ASSET_STATUS_IMPORTING);
		$dbAsset->save();
		
		$resource->attachCreatedObject($dbAsset);
		
		return $dbAsset;
	}
	
	/**
	 * @param string $entryFullPath
	 * @param entry $dbEntry
	 * @param asset $dbAsset
	 * @return asset
	 * @throws VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 */
	protected function attachFile($entryFullPath, entry $dbEntry, asset $dbAsset = null, $copyOnly = false)
	{
		$ext = pathinfo($entryFullPath, PATHINFO_EXTENSION);
		// TODO - move image handling to media service
		if($dbEntry->getMediaType() == VidiunMediaType::IMAGE)
		{
			$exifImageType = @exif_imagetype($entryFullPath);
			$validTypes = array(
				IMAGETYPE_JPEG,
				IMAGETYPE_TIFF_II,
				IMAGETYPE_TIFF_MM,
				IMAGETYPE_IFF,
				IMAGETYPE_PNG
			);
			
			if(in_array($exifImageType, $validTypes))
			{
				$exifData = @exif_read_data($entryFullPath);
				if ($exifData && isset($exifData["DateTimeOriginal"]) && $exifData["DateTimeOriginal"])
				{
					$mediaDate = $exifData["DateTimeOriginal"];
					
					// handle invalid dates either due to bad format or out of range
					if (!strtotime($mediaDate)){
						$mediaDate=null;
					}
					$dbEntry->setMediaDate($mediaDate);
				}
			}

			$allowedImageTypes = vConf::get("image_file_ext");
			if (in_array($ext, $allowedImageTypes))
				$dbEntry->setData("." . $ext);		
 			else		
 				$dbEntry->setData(".jpg");

			list($width, $height, $type, $attr) = getimagesize($entryFullPath);
			$dbEntry->setDimensions($width, $height);
			$dbEntry->setData(".jpg"); // this will increase the data version
			$dbEntry->save();
			$syncKey = $dbEntry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
			try
			{
				vFileSyncUtils::moveFromFile($entryFullPath, $syncKey, true, $copyOnly);
			}
			catch (Exception $e) {
				if($dbEntry->getStatus() == entryStatus::NO_CONTENT)
				{
					$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
					$dbEntry->save();
				}											
				throw $e;
			}
			
			$dbEntry->setStatus(entryStatus::READY);
			$dbEntry->save();	
				
			return null;
		}
		
		$isNewAsset = false;
		if(!$dbAsset)
		{
			$isNewAsset = true;
			$dbAsset = vFlowHelper::createOriginalFlavorAsset($this->getPartnerId(), $dbEntry->getId());
		}
		
		if(!$dbAsset && $dbEntry->getStatus() == entryStatus::NO_CONTENT)
		{
			$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
			$dbEntry->save();
		}
		
		$dbAsset->setFileExt($ext);
		
		if($dbAsset && ($dbAsset instanceof thumbAsset))
		{
			list($width, $height, $type, $attr) = getimagesize($entryFullPath);
			$dbAsset->setWidth($width);
			$dbAsset->setHeight($height);
			$dbAsset->save();
		}
		
		$syncKey = $dbAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		
		try
		{
			vFileSyncUtils::moveFromFile($entryFullPath, $syncKey, true, $copyOnly);
			$fileSync = vFileSyncUtils::getLocalFileSyncForKey($syncKey);
			$dbAsset->setSize($fileSync->getFileSize());
			$dbAsset->save();
		}
		catch (Exception $e) {
			
			if($dbEntry->getStatus() == entryStatus::NO_CONTENT)
			{
				$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
				$dbEntry->save();
			}
			
			$dbAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
			$dbAsset->save();												
			throw $e;
		}
		
		if($dbAsset && !($dbAsset instanceof flavorAsset))
		{
		    $dbAsset->setStatusLocalReady();
				
			if($dbAsset->getFlavorParamsId())
			{
				$dbFlavorParams = assetParamsPeer::retrieveByPK($dbAsset->getFlavorParamsId());
				if($dbFlavorParams)
					$dbAsset->setTags($dbFlavorParams->getTags());
			}
			$dbAsset->save();
		}
		
		if($isNewAsset)
			vEventsManager::raiseEvent(new vObjectAddedEvent($dbAsset));
		vEventsManager::raiseEvent(new vObjectDataChangedEvent($dbAsset));
			
		return $dbAsset;
	}
	
	/**
	 * @param FileSyncKey $srcSyncKey
	 * @param entry $dbEntry
	 * @param asset $dbAsset
	 * @param string $encryptionKey
	 * @return asset
	 * @throws VidiunErrors::ORIGINAL_FLAVOR_ASSET_NOT_CREATED
	 */
	protected function attachFileSync(FileSyncKey $srcSyncKey, entry $dbEntry, asset $dbAsset = null, $encryptionKey = null)
	{
		// TODO - move image handling to media service
		if($dbEntry->getMediaType() == VidiunMediaType::IMAGE)
		{
			$syncKey = $dbEntry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
	   		vFileSyncUtils::createSyncFileLinkForKey($syncKey, $srcSyncKey);
	   		
			$dbEntry->setStatus(entryStatus::READY);
			$dbEntry->save();	
				
			return null;
		}
		
	  	$isNewAsset = false;
	  	if(!$dbAsset)
	  	{
	  		$isNewAsset = true;
			$dbAsset = vFlowHelper::createOriginalFlavorAsset($this->getPartnerId(), $dbEntry->getId());

	  	}
	  	
		if(!$dbAsset)
		{
			VidiunLog::err("Flavor asset not created for entry [" . $dbEntry->getId() . "]");
			
			if($dbEntry->getStatus() == entryStatus::NO_CONTENT)
			{
				$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
				$dbEntry->save();
			}
			
			throw new VidiunAPIException(VidiunErrors::ORIGINAL_FLAVOR_ASSET_NOT_CREATED);
		}

		$newSyncKey = $dbAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		vFileSyncUtils::createSyncFileLinkForKey($newSyncKey, $srcSyncKey);

		if ($encryptionKey)
		{
			$dbAsset->setEncryptionKey($encryptionKey);
		}

		if($isNewAsset)
			vEventsManager::raiseEvent(new vObjectAddedEvent($dbAsset));
		vEventsManager::raiseEvent(new vObjectDataChangedEvent($dbAsset));
			
		return $dbAsset;
	}
	
	/**
	 * @param vOperationResource $resource
	 * @param entry $dbEntry
	 * @param asset $dbAsset
	 * @return asset
	 */
	protected function attachOperationResource(vOperationResource $resource, entry $dbEntry, asset $dbAsset = null)
	{
		$operationAttributes = $resource->getOperationAttributes();
		$internalResource = $resource->getResource();
		$srcEntry = self::getEntryFromContentResource($resource->getResource());
		$isLiveClippingFlow = $srcEntry && myEntryUtils::isLiveClippingEntry($srcEntry);
		if ($isLiveClippingFlow)
		{
			$this->handleLiveClippingFlow($srcEntry, $dbEntry, $operationAttributes);
		}
		elseif($internalResource instanceof vLiveEntryResource)
		{
			$dbAsset = $this->attachLiveEntryResource($internalResource, $dbEntry, $dbAsset, $operationAttributes);
		}
		else
		{
			$clipManager = new vClipManager();
			$this->handleMultiClipRequest($resource, $dbEntry, $clipManager, $operationAttributes);
		}
		return $dbAsset;
	}

	protected function handleLiveClippingFlow($recordedEntry, $clippedEntry, $operationAttributes)
	{
		if (($recordedEntry->getId() == $clippedEntry->getId()) || ($recordedEntry->getId() == $clippedEntry->getReplacedEntryId()))
			throw new VidiunAPIException(VidiunErrors::LIVE_CLIPPING_UNSUPPORTED_OPERATION, "Trimming");
		$clippedTask = $this->createRecordedClippingTask($recordedEntry, $clippedEntry, $operationAttributes);
		$clippedEntry->setSource(EntrySourceType::VIDIUN_RECORDED_LIVE);
		$clippedEntry->setConversionProfileId($recordedEntry->getConversionProfileId());
		$clippedEntry->setRootEntryId($recordedEntry->getRootEntryId());
		$clippedEntry->setIsRecordedEntry(true);
		$clippedEntry->setFlowType(EntryFlowType::LIVE_CLIPPING);
		$clippedEntry->setStatus(entryStatus::PENDING);
		$clippedEntry->save();
		return $clippedTask;
	}

	protected function createRecordedClippingTask(entry $srcEntry, entry $targetEntry, $operationAttributes)
	{
		$liveEntryId = $srcEntry->getRootEntryId();
		$entryServerNode = EntryServerNodePeer::retrieveByEntryIdAndServerType($liveEntryId, EntryServerNodeType::LIVE_PRIMARY);
		if (!$entryServerNode)
		{
			VidiunLog::debug("Can't create clipping task for SrcEntry: ". $srcEntry->getId() . " to entry:" . $targetEntry->getId() . " with: " . print_r($operationAttributes ,true));
			throw new VidiunAPIException(VidiunErrors::ENTRY_SERVER_NODE_NOT_FOUND, $liveEntryId, EntryServerNodeType::LIVE_PRIMARY);
		}
		$serverNode = ServerNodePeer::retrieveByPK($entryServerNode->getServerNodeId());

		$clippingTask = new ClippingTaskEntryServerNode();
		$clippingTask->setClippedEntryId($targetEntry->getId());
		$clippingTask->setLiveEntryId($liveEntryId);
		$clippingTask->setClipAttributes(self::getVClipAttributesForLiveClippingTask($operationAttributes));
		$clippingTask->setServerType(EntryServerNodeType::LIVE_CLIPPING_TASK);
		$clippingTask->setStatus(EntryServerNodeStatus::TASK_PENDING);
		$clippingTask->setEntryId($srcEntry->getId()); //recorded entry
		$clippingTask->setPartnerId($serverNode->getPartnerId()); //in case on eCDN it will get the local partner (not -5)
		$clippingTask->setServerNodeId($serverNode->getId());
		$clippingTask->save();
		return $clippingTask;
	}

	/**
	 * @param vContentResource $internalResource
	 * @return entry|null
	 */
	private static function getEntryFromContentResource($internalResource)
	{
		if ($internalResource && $internalResource instanceof vFileSyncResource)
		{
			$entryId = $internalResource->getOriginEntryId();
			if ($entryId)
				return entryPeer::retrieveByPK($entryId);
		}
		return null;
	}

	/**
	 * @return vClipAttributes
	 */
	protected static function getVClipAttributesForLiveClippingTask($operationAttributes)
	{
		if ($operationAttributes && count($operationAttributes) == 1 && $operationAttributes[0] instanceof vClipAttributes)
			return $operationAttributes[0];
		throw new VidiunAPIException(VidiunErrors::LIVE_CLIPPING_UNSUPPORTED_OPERATION, "Concat");
	}

	/**
	 * @param IRemoteStorageResource $resource
	 * @param entry $dbEntry
	 * @param asset $dbAsset
	 * @return asset
	 * @throws VidiunErrors::ORIGINAL_FLAVOR_ASSET_NOT_CREATED
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 */
	protected function attachRemoteStorageResource(IRemoteStorageResource $resource, entry $dbEntry, asset $dbAsset = null)
	{
		$resources = $resource->getResources();
		$fileExt = $resource->getFileExt();
		$dbEntry->setSource(VidiunSourceType::URL);
	
		// TODO - move image handling to media service
		if($dbEntry->getMediaType() == VidiunMediaType::IMAGE)
		{
			$syncKey = $dbEntry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
			foreach($resources as $currentResource)
			{
				$storageProfile = StorageProfilePeer::retrieveByPK($currentResource->getStorageProfileId());
				$fileSync = vFileSyncUtils::createReadyExternalSyncFileForKey($syncKey, $currentResource->getUrl(), $storageProfile);
			}
			
			$dbEntry->setStatus(entryStatus::READY);
			$dbEntry->save();
				
			return null;
		}
		$dbEntry->save();
		
	  	$isNewAsset = false;
	  	if(!$dbAsset)
	  	{
	  		$isNewAsset = true;
			$dbAsset = vFlowHelper::createOriginalFlavorAsset($this->getPartnerId(), $dbEntry->getId());
	  	}
	  	
		if(!$dbAsset)
		{
			VidiunLog::err("Flavor asset not created for entry [" . $dbEntry->getId() . "]");
			
			if($dbEntry->getStatus() == entryStatus::NO_CONTENT)
			{
				$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
				$dbEntry->save();
			}
			
			throw new VidiunAPIException(VidiunErrors::ORIGINAL_FLAVOR_ASSET_NOT_CREATED);
		}
				
		$syncKey = $dbAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
	
		foreach($resources as $currentResource)
		{
			$storageProfile = StorageProfilePeer::retrieveByPK($currentResource->getStorageProfileId());
			$fileSync = vFileSyncUtils::createReadyExternalSyncFileForKey($syncKey, $currentResource->getUrl(), $storageProfile);
		}

		$dbAsset->setFileExt($fileExt);
				
		if($dbAsset instanceof flavorAsset && !$dbAsset->getIsOriginal())
			$dbAsset->setStatus(asset::FLAVOR_ASSET_STATUS_READY);
			
		$dbAsset->save();
		
		if($isNewAsset)
			vEventsManager::raiseEvent(new vObjectAddedEvent($dbAsset));
		vEventsManager::raiseEvent(new vObjectDataChangedEvent($dbAsset));
			
		if($dbAsset instanceof flavorAsset && !$dbAsset->getIsOriginal())
			vBusinessPostConvertDL::handleConvertFinished(null, $dbAsset);
		
		return $dbAsset;
	}

	/**
	 * @param vUrlResource $resource
	 * @param entry $dbEntry
	 * @param asset $dbAsset
	 * @return asset
	 */
	protected function attachUrlResource(vUrlResource $resource, entry $dbEntry, asset $dbAsset = null)
	{
		$dbEntry->setSource(entry::ENTRY_MEDIA_SOURCE_URL);
		$dbEntry->save();

		$url = $resource->getUrl();

		if (!$resource->getForceAsyncDownload())
		{
			$ext = pathinfo($url, PATHINFO_EXTENSION);
			// TODO - move image handling to media service
    		if($dbEntry->getMediaType() == VidiunMediaType::IMAGE)
    		{
			    $entryFullPath = myContentStorage::getFSUploadsPath() . '/' . $dbEntry->getId() . '.' . $ext;
    			if (VCurlWrapper::getDataFromFile($url, $entryFullPath))
    				return $this->attachFile($entryFullPath, $dbEntry, $dbAsset);

    			VidiunLog::err("Failed downloading file[$url]");
    			$dbEntry->setStatus(entryStatus::ERROR_IMPORTING);
    			$dbEntry->save();

    			return null;
    		}

    		if($dbAsset && !($dbAsset instanceof flavorAsset))
    		{
    			$entryFullPath = myContentStorage::getFSUploadsPath() . '/' . $dbEntry->getId() . '.' . $ext;
    			if (VCurlWrapper::getDataFromFile($url, $entryFullPath))
    			{
    				$dbAsset = $this->attachFile($entryFullPath, $dbEntry, $dbAsset);
    				return $dbAsset;
    			}

    			VidiunLog::err("Failed downloading file[$url]");
    			$dbAsset->setStatus(asset::FLAVOR_ASSET_STATUS_ERROR);
    			$dbAsset->save();

    			return null;
    		}
		}

		vJobsManager::addImportJob(null, $dbEntry->getId(), $this->getPartnerId(), $url, $dbAsset, null, $resource->getImportJobData());

		return $dbAsset;
	}

	/**
	 * @param vAssetsParamsResourceContainers $resource
	 * @param entry $dbEntry
	 * @return asset
	 */
	protected function attachAssetsParamsResourceContainers(vAssetsParamsResourceContainers $resource, entry $dbEntry)
	{
		$ret = null;
		foreach($resource->getResources() as $assetParamsResourceContainer)
		{
			VidiunLog::debug("Resource asset params id [" . $assetParamsResourceContainer->getAssetParamsId() . "]");
			$dbAsset = $this->attachAssetParamsResourceContainer($assetParamsResourceContainer, $dbEntry);
			if(!$dbAsset)
				continue;

			VidiunLog::debug("Resource asset id [" . $dbAsset->getId() . "]");

			if($dbAsset->getIsOriginal())
				$ret = $dbAsset;
		}
		$dbEntry->save();

		return $ret;
	}

	/**
	 * @param vAssetParamsResourceContainer $resource
	 * @param entry $dbEntry
	 * @param asset $dbAsset
	 * @return asset
	 * @throws VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND
	 */
	protected function attachAssetParamsResourceContainer(vAssetParamsResourceContainer $resource, entry $dbEntry, asset $dbAsset = null)
	{
		$assetParams = assetParamsPeer::retrieveByPK($resource->getAssetParamsId());
		if(!$assetParams)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $resource->getAssetParamsId());
			
		if(!$dbAsset)
			$dbAsset = assetPeer::retrieveByEntryIdAndParams($dbEntry->getId(), $resource->getAssetParamsId());
			
		$isNewAsset = false;
		if(!$dbAsset)
		{
			$isNewAsset = true;
			$dbAsset = assetPeer::getNewAsset($assetParams->getType());
			$dbAsset->setPartnerId($dbEntry->getPartnerId());
			$dbAsset->setEntryId($dbEntry->getId());
			$dbAsset->setStatus(asset::FLAVOR_ASSET_STATUS_QUEUED);
			
			$dbAsset->setFlavorParamsId($resource->getAssetParamsId());
			$dbAsset->setFromAssetParams($assetParams);
			if($assetParams->hasTag(assetParams::TAG_SOURCE))
				$dbAsset->setIsOriginal(true);
		}
		$dbAsset->incrementVersion();
		$dbAsset->save();
		
		$dbAsset = $this->attachResource($resource->getResource(), $dbEntry, $dbAsset);
		
		if($dbAsset && $isNewAsset && $dbAsset->getStatus() != asset::FLAVOR_ASSET_STATUS_IMPORTING)
			vEventsManager::raiseEvent(new vObjectAddedEvent($dbAsset));
		
		return $dbAsset;
	}
	
	/**
	 * @param VidiunBaseEntry $entry
	 * @param entry $dbEntry
	 * @return entry
	 */
	protected function prepareEntryForInsert(VidiunBaseEntry $entry, entry $dbEntry = null)
	{
		// create a default name if none was given
		if (!$entry->name && !($dbEntry && $dbEntry->getName()))
			$entry->name = $this->getPartnerId().'_'.time();
			
		if ($entry->licenseType === null)
			$entry->licenseType = VidiunLicenseType::UNKNOWN;
		
		// first copy all the properties to the db entry, then we'll check for security stuff
		if(!$dbEntry)
		{
			$entryType = vPluginableEnumsManager::apiToCore('entryType', $entry->type);
			$class = entryPeer::getEntryClassByType($entryType);
				
			VidiunLog::debug("Creating new entry of API type [$entry->type] core type [$entryType] class [$class]");
			$dbEntry = new $class();
		}
			
		$dbEntry = $entry->toInsertableObject($dbEntry);

		$this->checkAndSetValidUserInsert($entry, $dbEntry);
		$this->checkAdminOnlyInsertProperties($entry);
		$this->validateAccessControlId($entry);
		$this->validateEntryScheduleDates($entry, $dbEntry);
			
		$dbEntry->setPartnerId($this->getPartnerId());
		$dbEntry->setSubpId($this->getPartnerId() * 100);
		$dbEntry->setDefaultModerationStatus();
				
		return $dbEntry;
	}
	
	/**
	 * Adds entry
	 * 
	 * @param VidiunBaseEntry $entry
	 * @return entry
	 */
	protected function add(VidiunBaseEntry $entry, $conversionProfileId = null)
	{
		$dbEntry = $this->duplicateTemplateEntry($conversionProfileId, $entry->templateEntryId);
		if ($dbEntry)
		{
			$dbEntry->save();
		}
		return $this->prepareEntryForInsert($entry, $dbEntry);
	}
	
	protected function duplicateTemplateEntry($conversionProfileId, $templateEntryId, $object_to_fill = null)
	{
		$templateEntry = $this->getTemplateEntry($conversionProfileId, $templateEntryId);
		if (!$object_to_fill)
			$object_to_fill = new entry();
		/* entry $baseTo */
		return $object_to_fill->copyTemplate(true, $templateEntry);
	}

	protected function getTemplateEntry($conversionProfileId, $templateEntryId)
	{
		if(!$templateEntryId)
		{
			$conversionProfile = myPartnerUtils::getConversionProfile2ForPartner($this->getPartnerId(), $conversionProfileId);
			if($conversionProfile)
				$templateEntryId = $conversionProfile->getDefaultEntryId();
		}
		if($templateEntryId)
		{
			$templateEntry = entryPeer::retrieveByPKNoFilter($templateEntryId, null, false);
			return $templateEntry;
		}
		return null;
	}
	
	/**
	 * Convert entry
	 * 
	 * @param string $entryId Media entry id
	 * @param int $conversionProfileId
	 * @param VidiunConversionAttributeArray $dynamicConversionAttributes
	 * @return bigint job id
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::FLAVOR_PARAMS_NOT_FOUND
	 */
	protected function convert($entryId, $conversionProfileId = null, VidiunConversionAttributeArray $dynamicConversionAttributes = null)
	{
		$entry = entryPeer::retrieveByPK($entryId);

		if (!$entry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		$srcFlavorAsset = assetPeer::retrieveOriginalByEntryId($entryId);
		if(!$srcFlavorAsset)
			throw new VidiunAPIException(VidiunErrors::ORIGINAL_FLAVOR_ASSET_IS_MISSING);
		
		if(is_null($conversionProfileId) || $conversionProfileId <= 0)
		{
			$conversionProfile = myPartnerUtils::getConversionProfile2ForEntry($entryId);
			if(!$conversionProfile)
				throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId);
			
			$conversionProfileId = $conversionProfile->getId();
		} 

		else {
			//The search is with the entry's partnerId. so if conversion profile wasn't found it means that the 
			//conversionId is not exist or the conversion profileId does'nt belong to this partner.
			$conversionProfile = conversionProfile2Peer::retrieveByPK ( $conversionProfileId );
			if (is_null ( $conversionProfile )) {
				throw new VidiunAPIException ( VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId );
			}
		}
		
		$srcSyncKey = $srcFlavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		
		// if the file sync isn't local (wasn't synced yet) proxy request to other datacenter
		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($srcSyncKey, true, false);
		if(!$fileSync)
		{
			throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);
		}
		else if(!$local)
		{
			vFileUtils::dumpApiRequest(vDataCenterMgr::getRemoteDcExternalUrl($fileSync));
		}
		
		// even if it null
		$entry->setConversionQuality($conversionProfileId);
		$entry->save();
		
		if($dynamicConversionAttributes)
		{
			$flavors = assetParamsPeer::retrieveByProfile($conversionProfileId);
			if(!count($flavors))
				throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_NOT_FOUND);
		
			$srcFlavorParamsId = null;
			$flavorParams = $entry->getDynamicFlavorAttributes();
			foreach($flavors as $flavor)
			{
				if($flavor->hasTag(flavorParams::TAG_SOURCE))
					$srcFlavorParamsId = $flavor->getId();
					
				$flavorParams[$flavor->getId()] = $flavor;
			}
			
			$dynamicAttributes = array();
			foreach($dynamicConversionAttributes as $dynamicConversionAttribute)
			{
				if(is_null($dynamicConversionAttribute->flavorParamsId))
					$dynamicConversionAttribute->flavorParamsId = $srcFlavorParamsId;
					
				if(is_null($dynamicConversionAttribute->flavorParamsId))
					continue;
					
				$dynamicAttributes[$dynamicConversionAttribute->flavorParamsId][trim($dynamicConversionAttribute->name)] = trim($dynamicConversionAttribute->value);
			}
			
			if(count($dynamicAttributes))
			{
				$entry->setDynamicFlavorAttributes($dynamicAttributes);
				$entry->save();
			}
		}
		
		$job = vJobsManager::addConvertProfileJob(null, $entry, $srcFlavorAsset->getId(), $fileSync);
		if(!$job)
			return null;
			
		return $job->getId();
	}
	
	protected function addEntryFromFlavorAsset(VidiunBaseEntry $newEntry, entry $srcEntry, flavorAsset $srcFlavorAsset)
	{
	  	$newEntry->type = $srcEntry->getType();
	  		
		if ($newEntry->name === null)
			$newEntry->name = $srcEntry->getName();
			
		if ($newEntry->description === null)
			$newEntry->description = $srcEntry->getDescription();
		
		if ($newEntry->creditUrl === null)
			$newEntry->creditUrl = $srcEntry->getSourceLink();
			
	   	if ($newEntry->creditUserName === null)
	   		$newEntry->creditUserName = $srcEntry->getCredit();
	   		
	 	if ($newEntry->tags === null)
	  		$newEntry->tags = $srcEntry->getTags();
	   		
		$newEntry->sourceType = VidiunSourceType::SEARCH_PROVIDER;
	 	$newEntry->searchProviderType = VidiunSearchProviderType::VIDIUN;
	 	
		$dbEntry = $this->prepareEntryForInsert($newEntry);
	  	$dbEntry->setSourceId( $srcEntry->getId() );
	  	
	 	$vshow = $this->createDummyVShow();
		$vshowId = $vshow->getId();
		
		$flavorAsset = vFlowHelper::createOriginalFlavorAsset($this->getPartnerId(), $dbEntry->getId());
		if(!$flavorAsset)
		{
			VidiunLog::err("Flavor asset not created for entry [" . $dbEntry->getId() . "]");
			
			if($dbEntry->getStatus() == entryStatus::NO_CONTENT)
			{
				$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
				$dbEntry->save();
			}
			
			throw new VidiunAPIException(VidiunErrors::ORIGINAL_FLAVOR_ASSET_NOT_CREATED);
		}
				
		$srcSyncKey = $srcFlavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$newSyncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		vFileSyncUtils::createSyncFileLinkForKey($newSyncKey, $srcSyncKey);

		vEventsManager::raiseEvent(new vObjectAddedEvent($flavorAsset));
				
		myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry);

		$newEntry->fromObject($dbEntry, $this->getResponseProfile());
		return $newEntry;
	}
	
	protected function getEntry($entryId, $version = -1, $entryType = null)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ($version !== -1)
			$dbEntry->setDesiredVersion($version);

		$vs = $this->getVs();
		$isAdmin = false;
		if($vs)
			$isAdmin = $vs->isAdmin();
		
		$entry = VidiunEntryFactory::getInstanceByType($dbEntry->getType(), $isAdmin);
		
		$entry->fromObject($dbEntry, $this->getResponseProfile());

		return $entry;
	}

	protected function getRemotePaths($entryId, $entryType = null)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ($dbEntry->getStatus() != entryStatus::READY)
			throw new VidiunAPIException(VidiunErrors::ENTRY_NOT_READY, $entryId);

		$c = new Criteria();
		$c->add(FileSyncPeer::OBJECT_TYPE, FileSyncObjectType::ENTRY);
		$c->add(FileSyncPeer::OBJECT_SUB_TYPE, entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
		$c->add(FileSyncPeer::OBJECT_ID, $entryId);
		$c->add(FileSyncPeer::VERSION, $dbEntry->getVersion());
		$c->add(FileSyncPeer::PARTNER_ID, $dbEntry->getPartnerId());
		$c->add(FileSyncPeer::STATUS, FileSync::FILE_SYNC_STATUS_READY);
		$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_URL);
		$fileSyncs = FileSyncPeer::doSelect($c);

		$listResponse = new VidiunRemotePathListResponse();
		$listResponse->objects = VidiunRemotePathArray::fromDbArray($fileSyncs, $this->getResponseProfile());
		$listResponse->totalCount = count($listResponse->objects);
		return $listResponse;
	}
	
	protected function listEntriesByFilter(VidiunBaseEntryFilter $filter = null, VidiunFilterPager $pager = null)
	{
		myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;

		$disableWidgetSessionFilters = false;
		if ($filter &&
			($filter->idEqual != null ||
			$filter->idIn != null ||
			$filter->referenceIdEqual != null ||
			$filter->redirectFromEntryId != null ||
			$filter->referenceIdIn != null || 
			$filter->parentEntryIdEqual != null))
			$disableWidgetSessionFilters = true;
			
		if (!$pager)
			$pager = new VidiunFilterPager();
		
		$c = $filter->prepareEntriesCriteriaFilter($pager);
		
		if ($disableWidgetSessionFilters)
		{
			if (vEntitlementUtils::getEntitlementEnforcement() && !vCurrentContext::$is_admin_session && entryPeer::getUserContentOnly())
				entryPeer::setFilterResults(true);

			VidiunCriterion::disableTag(VidiunCriterion::TAG_WIDGET_SESSION);
		}
		$list = entryPeer::doSelect($c);
		entryPeer::fetchPlaysViewsData($list);
		$totalCount = $c->getRecordsCount();
		
		if ($disableWidgetSessionFilters)
			VidiunCriterion::restoreTag(VidiunCriterion::TAG_WIDGET_SESSION);

		return array($list, $totalCount);		
	}
	
	protected function countEntriesByFilter(VidiunBaseEntryFilter $filter = null)
	{
		myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;

		if(!$filter)
			$filter = new VidiunBaseEntryFilter();
			
		$c = $filter->prepareEntriesCriteriaFilter();
		$c->applyFilters();
		$totalCount = $c->getRecordsCount();
		
		return $totalCount;
	}
	
	/*
	 	The following table shows the behavior of the checkAndSetValidUser functions:
	 	
	 	 otheruser - any user that is not the user specified in the vs
	  
	 	Input	 	 											Result	 
		Action			API entry user		DB entry user		Admin VS			User VS
		----------------------------------------------------------------------------------------
		entry.add		null / vsuser		N/A					vsuser				vsuser
 						otheruser			N/A					otheruser			exception
		entry.update	null / vsuser		vsuser				stays vsuser		stays vsuser
 						otheruser			vsuser				otheruser			exception
 						vsuser				otheruser			vsuser				exception
 						null / otheruser	otheruser			stays otheruser		if has edit privilege on entry => stays otheruser (checked by checkIfUserAllowedToUpdateEntry), 
 																					otherwise exception
	 */
	
   	/**
   	 * Sets the valid user for the entry 
   	 * Throws an error if the session user is trying to add entry to another user and not using an admin session 
   	 *
   	 * @param VidiunBaseEntry $entry
   	 * @param entry $dbEntry
   	 */
	protected function checkAndSetValidUserInsert(VidiunBaseEntry $entry, entry $dbEntry)
	{	
		// for new entry, puser ID is null - set it from service scope
		if ($entry->userId === null)
		{
			VidiunLog::debug("Set creator id [" . $this->getVuser()->getId() . "] line [" . __LINE__ . "]");
			$dbEntry->setCreatorVuserId($this->getVuser()->getId());
			$dbEntry->setCreatorPuserId($this->getVuser()->getPuserId());
			
			$dbEntry->setPuserId($this->getVuser()->getPuserId());
			$dbEntry->setVuserId($this->getVuser()->getId());
			return;
		}
		
		if ((!$this->getVs() || !$this->getVs()->isAdmin()))
		{
			// non admin cannot specify a different user on the entry other than himself
			$vsPuser = $this->getVuser()->getPuserId();
			if (strtolower($entry->userId) != strtolower($vsPuser))
			{
				throw new VidiunAPIException(VidiunErrors::INVALID_VS, "", vs::INVALID_TYPE, vs::getErrorStr(vs::INVALID_TYPE));
			}
		}


		// need to create vuser if this is an admin creating the entry on a different user
		$vuser = vuserPeer::createVuserForPartner($this->getPartnerId(), trim($entry->userId));
		$creatorId = is_null($entry->creatorId) ? $entry->creatorId : trim($entry->creatorId);
		$creator = vuserPeer::createVuserForPartner($this->getPartnerId(), $creatorId);

		VidiunLog::debug("Set vuser id [" . $vuser->getId() . "] line [" . __LINE__ . "]");
		$dbEntry->setVuserId($vuser->getId());
		$dbEntry->setCreatorVuserId($creator->getId());
		$dbEntry->setCreatorPuserId($creator->getPuserId());
	}
	
   	/**
   	 * Sets the valid user for the entry 
   	 * Throws an error if the session user is trying to update entry to another user and not using an admin session 
   	 *
   	 * @param VidiunBaseEntry $entry
   	 * @param entry $dbEntry
   	 */
	protected function checkAndSetValidUserUpdate(VidiunBaseEntry $entry, entry $dbEntry)
	{
		VidiunLog::debug("DB puser id [" . $dbEntry->getPuserId() . "] vuser id [" . $dbEntry->getVuserId() . "]");

		// user id not being changed
		if ($entry->userId === null)
		{
			VidiunLog::log("entry->userId is null, not changing user");
			return;
		}

		$vs = $this->getVs();
		if (!$vs ||(!$this->getVs()->isAdmin() && !$vs->verifyPrivileges(vs::PRIVILEGE_EDIT_USER, $entry->userId)))
		{
			$entryPuserId = $dbEntry->getPuserId();
			
			// non admin cannot change the owner of an existing entry
			if (strtolower($entry->userId) != strtolower($entryPuserId))
			{
				VidiunLog::debug('API entry userId ['.$entry->userId.'], DB entry userId ['.$entryPuserId.'] - change required but VS is not admin');
				throw new VidiunAPIException(VidiunErrors::INVALID_VS, "", vs::INVALID_TYPE, vs::getErrorStr(vs::INVALID_TYPE));
			}
		}
		
		// need to create vuser if this is an admin changing the owner of the entry to a different user
		$vuser = vuserPeer::createVuserForPartner($dbEntry->getPartnerId(), $entry->userId); 

		VidiunLog::debug("Set vuser id [" . $vuser->getId() . "] line [" . __LINE__ . "]");
		$dbEntry->setVuserId($vuser->getId());
	}
	
   	/**
   	 * Throws an error if the non-onwer session user is trying to update entitledPusersEdit or entitledPusersPublish 
   	 *
   	 * @param VidiunBaseEntry $entry
   	 * @param entry $dbEntry
   	 */
	protected function validateEntitledUsersUpdate(VidiunBaseEntry $entry, entry $dbEntry)
	{	
		if ((!$this->getVs() || !$this->getVs()->isAdmin()))
		{
			//non owner cannot change entitledUsersEdit and entitledUsersPublish
			if(!$dbEntry->isOwnerActionsAllowed($this->getVuser()->getId()))
			{
				if($entry->entitledUsersEdit !== null && strtolower($entry->entitledUsersEdit) != strtolower($dbEntry->getEntitledPusersEdit())){
					throw new VidiunAPIException(VidiunErrors::INVALID_VS, "", vs::INVALID_TYPE, vs::getErrorStr(vs::INVALID_TYPE));					
					
				}
				
				if($entry->entitledUsersPublish !== null && strtolower($entry->entitledUsersPublish) != strtolower($dbEntry->getEntitledPusersPublish())){
					throw new VidiunAPIException(VidiunErrors::INVALID_VS, "", vs::INVALID_TYPE, vs::getErrorStr(vs::INVALID_TYPE));					
					
				}
			}
		}
	}
	
	/**
	 * Throws an error if trying to update admin only properties with normal user session
	 *
	 * @param VidiunBaseEntry $entry
	 */
	protected function checkAdminOnlyUpdateProperties(VidiunBaseEntry $entry)
	{
		if ($entry->adminTags !== null)
		{
			$vs = $this->getVs();
			if (!$vs || !$vs->verifyPrivileges(vs::PRIVILEGE_EDIT_ADMIN_TAGS, vs::PRIVILEGE_WILDCARD ))
				$this->validateAdminSession("adminTags");
		}

		if ($entry->categories !== null)
		{
			$cats = explode(entry::ENTRY_CATEGORY_SEPARATOR, $entry->categories);
			foreach($cats as $cat)
			{
				if(!categoryPeer::getByFullNameExactMatch($cat))
					$this->validateAdminSession("categories");
			}
		}
			
		if ($entry->startDate !== null)
			$this->validateAdminSession("startDate");
			
		if  ($entry->endDate !== null)
			$this->validateAdminSession("endDate");
	}
	
	/**
	 * Throws an error if trying to update admin only properties with normal user session
	 *
	 * @param VidiunBaseEntry $entry
	 */
	protected function checkAdminOnlyInsertProperties(VidiunBaseEntry $entry)
	{
		if ($entry->adminTags !== null)
		{
			$vs = $this->getVs();
			if (!$vs || !$vs->verifyPrivileges(vs::PRIVILEGE_EDIT_ADMIN_TAGS, vs::PRIVILEGE_WILDCARD ))
				$this->validateAdminSession("adminTags");
		}

		if ($entry->categories !== null)
		{
			$cats = explode(entry::ENTRY_CATEGORY_SEPARATOR, $entry->categories);
			foreach($cats as $cat)
			{
				if(!categoryPeer::getByFullNameExactMatch($cat))
					$this->validateAdminSession("categories");
			}
		}
			
		if ($entry->startDate !== null)
			$this->validateAdminSession("startDate");
			
		if  ($entry->endDate !== null)
			$this->validateAdminSession("endDate");
	}
	
	/**
	 * Validates that current session is an admin session 
	 */
	protected function validateAdminSession($property)
	{
		if (!$this->getVs() || !$this->getVs()->isAdmin())
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_ADMIN_PROPERTY, $property);	
	}
	
	/**
	 * Throws an error if trying to set invalid Access Control Profile
	 * 
	 * @param VidiunBaseEntry $entry
	 */
	protected function validateAccessControlId(VidiunBaseEntry $entry)
	{
		if ($entry->accessControlId !== null) // trying to update
		{
			$this->applyPartnerFilterForClass('accessControl'); 
			$accessControl = accessControlPeer::retrieveByPK($entry->accessControlId);
			if (!$accessControl)
				throw new VidiunAPIException(VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND, $entry->accessControlId);
		}
	}
	
	/**
	 * Throws an error if trying to set invalid entry schedule date
	 * 
	 * @param VidiunBaseEntry $entry
	 */
	protected function validateEntryScheduleDates(VidiunBaseEntry $entry, entry $dbEntry)
	{
		if(is_null($entry->startDate) && is_null($entry->endDate))
			return; // no update

		if($entry->startDate instanceof VidiunNullField)
			$entry->startDate = -1;
		if($entry->endDate instanceof VidiunNullField)
			$entry->endDate = -1;
			
		// if input is null and this is an update pick the current db value 
		$startDate = is_null($entry->startDate) ?  $dbEntry->getStartDate(null) : $entry->startDate;
		$endDate = is_null($entry->endDate) ?  $dbEntry->getEndDate(null) : $entry->endDate;
		
		// normalize values for valid comparison later 
		if ($startDate < 0)
			$startDate = null;
		
		if ($endDate < 0)
			$endDate = null;
		
		if ($startDate && $endDate && $startDate >= $endDate)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_SCHEDULE_DATES);
		}
	}
	

	protected function createDummyVShow()
	{
		$vshow = new vshow();
		$vshow->setName(vshow::DUMMY_VSHOW_NAME);
		$vshow->setProducerId($this->getVuser()->getId());
		$vshow->setPartnerId($this->getPartnerId());
		$vshow->setSubpId($this->getPartnerId() * 100);
		$vshow->setViewPermissions(vshow::VSHOW_PERMISSION_EVERYONE);
		$vshow->setPermissions(vshow::PERMISSIONS_PUBLIC);
		$vshow->setAllowQuickEdit(true);
		$vshow->save();
		
		return $vshow;
	}
	
	protected function updateEntry($entryId, VidiunBaseEntry $entry, $entryType = null)
	{
		$entry->type = null; // because it was set in the constructor, but cannot be updated
		
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		
		$this->checkAndSetValidUserUpdate($entry, $dbEntry);
		$this->checkAdminOnlyUpdateProperties($entry);
		$this->validateEntitledUsersUpdate($entry, $dbEntry);
		$this->validateAccessControlId($entry);
		$this->validateEntryScheduleDates($entry, $dbEntry); 
		
		$dbEntry = $entry->toUpdatableObject($dbEntry);
		/* @var $dbEntry entry */
		
		$updatedOccurred = $dbEntry->save();
		$entry->fromObject($dbEntry, $this->getResponseProfile());
		
		try 
		{
			$wrapper = objectWrapperBase::getWrapperClass($dbEntry);
			$wrapper->removeFromCache("entry", $dbEntry->getId());
		}
		catch(Exception $e)
		{
			VidiunLog::err($e);
		}
		
		if ($updatedOccurred)
		{
			myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE, $dbEntry);
		}
		
		return $entry;
	}
	
	protected function deleteEntry($entryId, $entryType = null)
	{
		$entryToDelete = entryPeer::retrieveByPK($entryId);

		if (!$entryToDelete || ($entryType !== null && $entryToDelete->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		myEntryUtils::deleteEntry($entryToDelete);
		
		try
		{
			$wrapper = objectWrapperBase::getWrapperClass($entryToDelete);
			$wrapper->removeFromCache("entry", $entryToDelete->getId());
		}
		catch(Exception $e)
		{
			VidiunLog::err($e);
		}
	}
	
	protected function updateThumbnailForEntryFromUrl($entryId, $url, $entryType = null, $fileSyncType = entry::FILE_SYNC_ENTRY_SUB_TYPE_THUMB)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		// if session is not admin, we should check that the user that is updating the thumbnail is the one created the entry
		// FIXME: Temporary disabled because update thumbnail feature (in app studio) is working with anonymous vs
		/*if (!$this->getVs()->isAdmin())
		{
			if ($dbEntry->getPuserId() !== $this->getVs()->user)
			{
				throw new VidiunAPIException(VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY);
			}
		}*/

		$content = VCurlWrapper::getContent($url);
		if (!$content)
		{
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_DOWNLOAD_FAILED, $url);
		}
		myEntryUtils::updateThumbnailFromContent($dbEntry, $content, $fileSyncType);
		
		$entry = VidiunEntryFactory::getInstanceByType($dbEntry->getType());
		$entry->fromObject($dbEntry, $this->getResponseProfile());
		
		return $entry;
	}
	
	protected function updateThumbnailJpegForEntry($entryId, $fileData, $entryType = null, $fileSyncType = entry::FILE_SYNC_ENTRY_SUB_TYPE_THUMB)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		// if session is not admin, we should check that the user that is updating the thumbnail is the one created the entry
		// FIXME: Temporary disabled because update thumbnail feature (in app studio) is working with anonymous vs
		/*if (!$this->getVs()->isAdmin())
		{
			if ($dbEntry->getPuserId() !== $this->getVs()->user)
			{
				throw new VidiunAPIException(VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY);
			}
		}*/
		myEntryUtils::updateThumbnailFromContent($dbEntry, file_get_contents($fileData["tmp_name"]), $fileSyncType);
		
		$entry = VidiunEntryFactory::getInstanceByType($dbEntry->getType());
		$entry->fromObject($dbEntry, $this->getResponseProfile());
		
		return $entry;
	}
	
	protected function updateThumbnailForEntryFromSourceEntry($entryId, $sourceEntryId, $timeOffset, $entryType = null, $flavorParamsId = null)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		$sourceDbEntry = entryPeer::retrieveByPK($sourceEntryId);
		if (!$sourceDbEntry || $sourceDbEntry->getType() != VidiunEntryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $sourceDbEntry);
			
		// if session is not admin, we should check that the user that is updating the thumbnail is the one created the entry
		if (!$this->getVs() || !$this->getVs()->isAdmin())
		{
			if (strtolower($dbEntry->getPuserId()) !== strtolower($this->getVs()->user))
			{
				throw new VidiunAPIException(VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY);
			}
		}
		
		$updateThumbnailResult = myEntryUtils::createThumbnailFromEntry($dbEntry, $sourceDbEntry, $timeOffset, $flavorParamsId);
		
		if (!$updateThumbnailResult)
		{
			throw new VidiunAPIException(VidiunErrors::INTERNAL_SERVERL_ERROR);
		}
		
		try
		{
			$wrapper = objectWrapperBase::getWrapperClass($dbEntry);
			$wrapper->removeFromCache("entry", $dbEntry->getId());
		}
		catch(Exception $e)
		{
			VidiunLog::err($e);
		}
		
		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE_THUMBNAIL, $dbEntry, $dbEntry->getPartnerId(), $dbEntry->getPuserId(), null, null, $entryId);

		$vs = $this->getVs();
		$isAdmin = false;
		if($vs)
			$isAdmin = $vs->isAdmin();
			
		$mediaEntry = VidiunEntryFactory::getInstanceByType($dbEntry->getType(), $isAdmin);
		$mediaEntry->fromObject($dbEntry, $this->getResponseProfile());
		
		return $mediaEntry;
	}
	
	protected function flagEntry(VidiunModerationFlag $moderationFlag, $entryType = null)
	{
		$moderationFlag->validatePropertyNotNull("flaggedEntryId");

		$entryId = $moderationFlag->flaggedEntryId;
		$dbEntry = vCurrentContext::initPartnerByEntryId($entryId);

		// before returning any error, let's validate partner's access control
		if ($dbEntry)
			$this->validateApiAccessControl($dbEntry->getPartnerId());

		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$validModerationStatuses = array(
			VidiunEntryModerationStatus::APPROVED,
			VidiunEntryModerationStatus::AUTO_APPROVED,
			VidiunEntryModerationStatus::FLAGGED_FOR_REVIEW,
		);
		if (!in_array($dbEntry->getModerationStatus(), $validModerationStatuses))
			throw new VidiunAPIException(VidiunErrors::ENTRY_CANNOT_BE_FLAGGED);
			
		$dbModerationFlag = new moderationFlag();
		$dbModerationFlag->setPartnerId($dbEntry->getPartnerId());
		$dbModerationFlag->setVuserId($this->getVuser()->getId());
		$dbModerationFlag->setFlaggedEntryId($dbEntry->getId());
		$dbModerationFlag->setObjectType(VidiunModerationObjectType::ENTRY);
		$dbModerationFlag->setStatus(VidiunModerationFlagStatus::PENDING);
		$dbModerationFlag->setFlagType($moderationFlag->flagType);
		$dbModerationFlag->setComments($moderationFlag->comments);
		$dbModerationFlag->save();
		
		$dbEntry->setModerationStatus(VidiunEntryModerationStatus::FLAGGED_FOR_REVIEW);
		$updateOccurred = $dbEntry->save();
		
		$moderationFlag = new VidiunModerationFlag();
		$moderationFlag->fromObject($dbModerationFlag, $this->getResponseProfile());
		
		// need to notify the partner that an entry was flagged - use the OLD moderation onject that is required for the 
		// NOTIFICATION_TYPE_ENTRY_REPORT notification
		// TODO - change to moderationFlag object to implement the interface for the notification:
		// it should have "objectId", "comments" , "reportCode" as getters
		$oldModerationObj = new moderation();
		$oldModerationObj->setPartnerId($dbEntry->getPartnerId());
		$oldModerationObj->setComments( $moderationFlag->comments);
		$oldModerationObj->setObjectId( $dbEntry->getId() );
		$oldModerationObj->setObjectType( moderation::MODERATION_OBJECT_TYPE_ENTRY );
		$oldModerationObj->setReportCode( "" );
		if ($updateOccurred)
			myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_ENTRY_REPORT, $oldModerationObj ,$dbEntry->getPartnerId());
				
		return $moderationFlag;
	}
	
	protected function rejectEntry($entryId, $entryType = null)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		$dbEntry->setModerationStatus(VidiunEntryModerationStatus::REJECTED);
		$dbEntry->setModerationCount(0);
		$updateOccurred = $dbEntry->save();
		
		if ($updateOccurred)
			myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE , $dbEntry, null, null, null, null, $dbEntry->getId() );
//		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_BLOCK , $dbEntry->getId());
		
		moderationFlagPeer::markAsModeratedByEntryId($this->getPartnerId(), $dbEntry->getId());
	}
	
	protected function approveEntry($entryId, $entryType = null)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		$dbEntry->setModerationStatus(VidiunEntryModerationStatus::APPROVED);
		$dbEntry->setModerationCount(0);
		$updateOccurred = $dbEntry->save();
		
		if ($updateOccurred)
			myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE , $dbEntry, null, null, null, null, $dbEntry->getId() );
//		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_BLOCK , $dbEntry->getId());
		
		moderationFlagPeer::markAsModeratedByEntryId($this->getPartnerId(), $dbEntry->getId());
	}
	
	protected function listFlagsForEntry($entryId, VidiunFilterPager $pager = null)
	{
		if (!$pager)
			$pager = new VidiunFilterPager();
			
		$c = new Criteria();
		$c->addAnd(moderationFlagPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(moderationFlagPeer::FLAGGED_ENTRY_ID, $entryId);
		$c->addAnd(moderationFlagPeer::OBJECT_TYPE, VidiunModerationObjectType::ENTRY);
		$c->addAnd(moderationFlagPeer::STATUS, VidiunModerationFlagStatus::PENDING);
		
		$totalCount = moderationFlagPeer::doCount($c);
		$pager->attachToCriteria($c);
		$list = moderationFlagPeer::doSelect($c);
		
		$newList = VidiunModerationFlagArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunModerationFlagListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
	
	protected function anonymousRankEntry($entryId, $entryType = null, $rank)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry || ($entryType !== null && $dbEntry->getType() != $entryType))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		if ($rank <= 0 || $rank > 5)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_RANK_VALUE);
		}

		$vvote = new vvote();
		$vvote->setEntryId($entryId);
		$vvote->setVuserId($this->getVuser()->getId());
		$vvote->setRank($rank);
		$vvote->save();
	}

	/**
	 * @param $resource
	 * @param entry $dbEntry
	 * @param $clipManager
	 * @param $operationAttributes
	 * @return asset
	 * @throws VidiunAPIException
	 */
	protected function handleMultiClipRequest($resource, entry $dbEntry, $clipManager, $operationAttributes)
	{
		VidiunLog::info("clipping service detected start to create sub flavors;");
		$clipEntry = $clipManager->createTempEntryForClip($this->getPartnerId());
		$shouldimport = false;
		$url = null;
		if ($resource->getResource() instanceof vFileSyncResource && $resource->getResource()->getOriginEntryId())
		{
			$url = $this->getImportUrl($resource->getResource()->getOriginEntryId());
		}
		if (!$url)
		{
			$clipDummySourceAsset = vFlowHelper::createOriginalFlavorAsset($this->getPartnerId(), $clipEntry->getId());
			$this->attachResource($resource->getResource(), $clipEntry, $clipDummySourceAsset);
		}
		$clipManager->startBatchJob($resource, $dbEntry, $operationAttributes, $clipEntry , $url);
	}

	/***
	 * @param null $entryId
	 * @return string $url
	 * @throws Exception
	 */
	protected function getImportUrl($entryId = null)
	{
		if ($entryId)
		{
			$originalFlavorAsset = assetPeer::retrieveOriginalReadyByEntryId($entryId);
			if ($originalFlavorAsset)
			{
				$srcSyncKey = $originalFlavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
				list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($srcSyncKey, true, false);
				/* @var $fileSync FileSync */
				if ($fileSync && !$local)
				{
					return $fileSync->getExternalUrl($entryId);
				}
			}
		}
		return null;
	}

	/**
	 * Set the default status to ready if other status filters are not specified
	 * 
	 * @param VidiunBaseEntryFilter $filter
	 */
	private function setDefaultStatus(VidiunBaseEntryFilter $filter)
	{
		if ($filter->statusEqual === null && 
			$filter->statusIn === null &&
			$filter->statusNotEqual === null &&
			$filter->statusNotIn === null)
		{
			$filter->statusEqual = VidiunEntryStatus::READY;
		}
	}
	
	/**
	 * Set the default moderation status to ready if other moderation status filters are not specified
	 * 
	 * @param VidiunBaseEntryFilter $filter
	 */
	private function setDefaultModerationStatus(VidiunBaseEntryFilter $filter)
	{
		if ($filter->moderationStatusEqual === null && 
			$filter->moderationStatusIn === null && 
			$filter->moderationStatusNotEqual === null && 
			$filter->moderationStatusNotIn === null)
		{
			$moderationStatusesNotIn = array(
				VidiunEntryModerationStatus::PENDING_MODERATION, 
				VidiunEntryModerationStatus::REJECTED);
			$filter->moderationStatusNotIn = implode(",", $moderationStatusesNotIn); 
		}
	}
	
	/**
	 * Convert duration in seconds to msecs (because the duration field is mapped to length_in_msec)
	 * 
	 * @param VidiunBaseEntryFilter $filter
	 */
	private function fixFilterDuration(VidiunBaseEntryFilter $filter)
	{
		if ($filter instanceof VidiunPlayableEntryFilter) // because duration filter should be supported in baseEntryService
		{
			if ($filter->durationGreaterThan !== null)
				$filter->durationGreaterThan = $filter->durationGreaterThan * 1000;

			//When translating from seconds to msec need to subtract 500 msec since entries greater than 5500 msec are considered as entries with 6 sec
			if ($filter->durationGreaterThanOrEqual !== null)
				$filter->durationGreaterThanOrEqual = $filter->durationGreaterThanOrEqual * 1000 - 500;
				
			if ($filter->durationLessThan !== null)
				$filter->durationLessThan = $filter->durationLessThan * 1000;
				
			//When translating from seconds to msec need to add 499 msec since entries less than 5499 msec are considered as entries with 5 sec
			if ($filter->durationLessThanOrEqual !== null)
				$filter->durationLessThanOrEqual = $filter->durationLessThanOrEqual * 1000 + 499;
		}
	}
	
	// hack due to VCW of version  from VMC
	protected function getConversionQualityFromRequest () 
	{
		if(isset($_REQUEST["conversionquality"]))
			return $_REQUEST["conversionquality"];
		return null;
	}

	protected function validateContent($dbEntry)
	{
		try
		{
			myEntryUtils::validateObjectContent($dbEntry);
		}
		catch (Exception $e)
		{
			$dbEntry->setStatus(entryStatus::ERROR_IMPORTING);
			$dbEntry->save();
			throw new VidiunAPIException(VidiunErrors::IMAGE_CONTENT_NOT_SECURE);
		}
	}

}

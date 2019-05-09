<?php

/**
 * Data service lets you manage data content (textual content)
 *
 * @service data
 * @package api
 * @subpackage services
 */
class DataService extends VidiunEntryService
{
	
	protected function vidiunNetworkAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		return parent::vidiunNetworkAllowed($actionName);
	}
	
	
	/**
	 * Adds a new data entry
	 * 
	 * @action add
	 * @param VidiunDataEntry $dataEntry Data entry
	 * @return VidiunDataEntry The new data entry
	 */
	function addAction(VidiunDataEntry $dataEntry)
	{
		$dbEntry = $dataEntry->toObject(new entry());
		
		$this->checkAndSetValidUserInsert($dataEntry, $dbEntry);
		$this->checkAdminOnlyInsertProperties($dataEntry);
		$this->validateAccessControlId($dataEntry);
		$this->validateEntryScheduleDates($dataEntry, $dbEntry);
		
		$dbEntry->setPartnerId($this->getPartnerId());
		$dbEntry->setSubpId($this->getPartnerId() * 100);
		$dbEntry->setStatus(VidiunEntryStatus::READY);
		$dbEntry->setMediaType(entry::ENTRY_MEDIA_TYPE_AUTOMATIC); 
		$dbEntry->save();
		
		$trackEntry = new TrackEntry();
		$trackEntry->setEntryId($dbEntry->getId());
		$trackEntry->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY);
		$trackEntry->setDescription(__METHOD__ . ":" . __LINE__ . "::ENTRY_DATA");
		TrackEntry::addTrackEntry($trackEntry);
		
		$dataEntry->fromObject($dbEntry, $this->getResponseProfile());
		
		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry);
		
		return $dataEntry;
	}
	
	/**
	 * Get data entry by ID.
	 * 
	 * @action get
	 * @param string $entryId Data entry id
	 * @param int $version Desired version of the data
	 * @return VidiunDataEntry The requested data entry
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function getAction($entryId, $version = -1)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::DATA)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ($version !== -1)
			$dbEntry->setDesiredVersion($version);
			
		$dataEntry = new VidiunDataEntry();
		$dataEntry->fromObject($dbEntry, $this->getResponseProfile());

		return $dataEntry;
	}
	
	/**
	 * Update data entry. Only the properties that were set will be updated.
	 * 
	 * @action update
	 * @param string $entryId Data entry id to update
	 * @param VidiunDataEntry $documentEntry Data entry metadata to update
	 * @return VidiunDataEntry The updated data entry
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * validateUser entry $entryId edit
	 */
	function updateAction($entryId, VidiunDataEntry $documentEntry)
	{
		return $this->updateEntry($entryId, $documentEntry, VidiunEntryType::DATA);
	}
	
	/**
	 * Delete a data entry.
	 *
	 * @action delete
	 * @param string $entryId Data entry id to delete
	 * 
 	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
 	 * @validateUser entry entryId edit
	 */
	function deleteAction($entryId)
	{
		$this->deleteEntry($entryId, VidiunEntryType::DATA);
	}
	
	/**
	 * List data entries by filter with paging support.
	 * 
	 * @action list
     * @param VidiunDataEntryFilter $filter Document entry filter
	 * @param VidiunFilterPager $pager Pager
	 * @return VidiunDataListResponse Wrapper for array of document entries and total count
	 */
	function listAction(VidiunDataEntryFilter $filter = null, VidiunFilterPager $pager = null)
	{
	    if (!$filter)
			$filter = new VidiunDataEntryFilter();
			
	    $filter->typeEqual = VidiunEntryType::DATA;
	    list($list, $totalCount) = parent::listEntriesByFilter($filter, $pager);
	    
	    $newList = VidiunDataEntryArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunDataListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/**
	 * return the file from dataContent field.
	 * 
	 * @action serve
	 * @param string $entryId Data entry id
	 * @param int $version Desired version of the data
	 * @param bool $forceProxy force to get the content without redirect
	 * @return file
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function serveAction($entryId, $version = -1, $forceProxy = false)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::DATA)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$vsObj = $this->getVs();
		$vs = ($vsObj) ? $vsObj->getOriginalString() : null;
		$securyEntryHelper = new VSecureEntryHelper($dbEntry, $vs, null, ContextType::DOWNLOAD);
		$securyEntryHelper->validateForDownload();	
		
		if ( ! $version || $version == -1 ) $version = null;
		
		$fileName = $dbEntry->getName();
		
		$syncKey = $dbEntry->getSyncKey( entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA , $version);
		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
		
		header("Content-Disposition: attachment; filename=\"$fileName\"");

		if($local)
		{
			$filePath = $fileSync->getFullPath();
			$mimeType = vFile::mimeType($filePath);
			$key = $fileSync->isEncrypted() ? $fileSync->getEncryptionKey() : null;
			$iv = $key ? $fileSync->getIv() : null;
			return $this->dumpFile($filePath, $mimeType, $key, $iv);
		}
		else
		{
			$remoteUrl = vDataCenterMgr::getRedirectExternalUrl($fileSync);
			VidiunLog::info("Redirecting to [$remoteUrl]");
			if($forceProxy)
			{
				vFileUtils::dumpUrl($remoteUrl);
			}
			else
			{
				// or redirect if no proxy
				header("Location: $remoteUrl");
				die;
			}
		}	
	}


	/**
	* Update the dataContent of data entry using a resource
	*
	* @action addContent
	* @param string $entryId
	* @param VidiunGenericDataCenterContentResource $resource
	* @return string
	* @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	* @validateUser entry entryId edit
	*/
	function addContentAction($entryId, VidiunGenericDataCenterContentResource $resource)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ($dbEntry->getType() != VidiunEntryType::DATA)
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_TYPE,$entryId, $dbEntry->getType(), entryType::DATA);

		$resource->validateEntry($dbEntry);
		$vResource = $resource->toObject();
		$this->attachResource($vResource, $dbEntry);
		$resource->entryHandled($dbEntry);

		return $this->getEntry($entryId);
	}

	/**
	* @param vResource $resource
	* @param entry $dbEntry
	* @param asset $dbAsset
	* @return asset
	*/
	protected function attachResource(vResource $resource, entry $dbEntry, asset $dbAsset = null)
	{
		if(($resource->getType() == 'vLocalFileResource') && (!isset($resource->getSourceType) ||  $resource->getSourceType != VidiunSourceType::WEBCAM))
		{
			$file_path = $resource->getLocalFilePath();
			$fileType = vFile::mimeType($file_path);
			if((substr($fileType, 0, 5) == 'text/') || ($fileType == 'application/xml')) {
				$dbEntry->setDataContent(vFile::getFileContent($file_path));
			}
			else{
				VidiunLog::err("Resource of type [" . get_class($resource) . "] with file type ". $fileType. " is not supported");
				throw new VidiunAPIException(VidiunErrors::FILE_TYPE_NOT_SUPPORTED, $fileType);
			}
		}
		else
		{
			VidiunLog::err("Resource of type [" . get_class($resource) . "] is not supported");
			throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($resource));
		}
	}
}

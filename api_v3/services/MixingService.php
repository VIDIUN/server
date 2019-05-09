<?php

/**
 * A Mix is an XML unique format invented by Vidiun, it allows the user to create a mix of videos and images, in and out points, transitions, text overlays, soundtrack, effects and much more...
 * Mixing service lets you create a new mix, manage its metadata and make basic manipulations.   
 *
 * @service mixing
 * @package api
 * @subpackage services
 */
class MixingService extends VidiunEntryService
{
	
	protected function vidiunNetworkAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		return parent::vidiunNetworkAllowed($actionName);
	}
	
	/**
	 * Adds a new mix.
	 * If the dataContent is null, a default timeline will be created.
	 * 
	 * @action add
	 * @param VidiunMixEntry $mixEntry Mix entry metadata
	 * @return VidiunMixEntry The new mix entry
	 */
	function addAction(VidiunMixEntry $mixEntry)
	{
		$mixEntry->validatePropertyMinLength("name", 1);
		$mixEntry->validatePropertyNotNull("editorType");
		
		$dbEntry = $mixEntry->toObject(new entry());
		
		$this->checkAndSetValidUserInsert($mixEntry, $dbEntry);
		$this->checkAdminOnlyInsertProperties($mixEntry);
		$this->validateAccessControlId($mixEntry);
		$this->validateEntryScheduleDates($mixEntry, $dbEntry);
		
		$vshow = $this->createDummyVShow();

		$dbEntry->setVshowId($vshow->getId());
		$dbEntry->setPartnerId($this->getPartnerId());
		$dbEntry->setSubpId($this->getPartnerId() * 100);
		$dbEntry->setStatus(VidiunEntryStatus::READY);
		$dbEntry->setMediaType(entry::ENTRY_MEDIA_TYPE_SHOW); // for backward compatibility

		if (!$dbEntry->getThumbnail())
		{
			$dbEntry->setThumbnail("&auto_edit.jpg");
			$dbEntry->setCreateThumb(false);
		}
			
		$dbEntry->save(); // we need the id for setDataContent
		
		// set default data if no data given
		if ($mixEntry->dataContent === null)
		{
			myEntryUtils::modifyEntryMetadataWithText($dbEntry, "", 0);
		}
		else
		{ 
			$dbEntry->setDataContent($mixEntry->dataContent, true, true);
			$dbEntry->save();
		}
		
		$trackEntry = new TrackEntry();
		$trackEntry->setEntryId($dbEntry->getId());
		$trackEntry->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY);
		$trackEntry->setDescription(__METHOD__ . ":" . __LINE__ . "::ENTRY_MIX");
		TrackEntry::addTrackEntry($trackEntry);
		
		$vshow->setShowEntry($dbEntry);
		$vshow->save();
		$mixEntry->fromObject($dbEntry, $this->getResponseProfile());
		
		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry);
		
		return $mixEntry;
	}
	
	/**
	 * Get mix entry by id.
	 * 
	 * @action get
	 * @param string $entryId Mix entry id
	 * @param int $version Desired version of the data
	 * @return VidiunMixEntry The requested mix entry
	 */
	function getAction($entryId, $version = -1)
	{
		return $this->getEntry($entryId, $version, VidiunEntryType::MIX);
	}
	
	/**
	 * Update mix entry. Only the properties that were set will be updated.
	 * 
	 * @action update
	 * @param string $entryId Mix entry id to update
	 * @param VidiunMixEntry $mixEntry Mix entry metadata to update
	 * @return VidiunMixEntry The updated mix entry
	 * @validateUser entry entryId edit
	 */
	function updateAction($entryId, VidiunMixEntry $mixEntry)
	{
		$mixEntry->type = null; // because it was set in the constructor, but cannot be updated
		
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::MIX)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		
		$this->checkAndSetValidUserUpdate($mixEntry, $dbEntry);
		$this->checkAdminOnlyUpdateProperties($mixEntry);
		$this->validateAccessControlId($mixEntry);
		$this->validateEntryScheduleDates($mixEntry, $dbEntry);
		
		$dbEntry = $mixEntry->toUpdatableObject($dbEntry);
		/* @var $dbEntry entry */
		
		if ($mixEntry->dataContent !== null) // dataContent need special handling
			$dbEntry->setDataContent($mixEntry->dataContent, true, true);
			
		$dbEntry->save();
		$mixEntry->fromObject($dbEntry, $this->getResponseProfile());
		
		try
		{
			$wrapper = objectWrapperBase::getWrapperClass($dbEntry);
			$wrapper->removeFromCache("entry", $dbEntry->getId());
		}
		catch(Exception $e)
		{
			VidiunLog::err($e);
		}
		
		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE, $dbEntry);
		
		return $mixEntry;
	}
	
	/**
	 * Delete a mix entry.
	 *
	 * @action delete
	 * @param string $entryId Mix entry id to delete
	 * @validateUser entry entryId edit
	 */
	function deleteAction($entryId)
	{
		$this->deleteEntry($entryId, VidiunEntryType::MIX);
	}
	
	/**
	 * List entries by filter with paging support.
	 * Return parameter is an array of mix entries.
	 * 
	 * @action list
	 * @param VidiunMixEntryFilter $filter Mix entry filter
	 * @param VidiunFilterPager $pager Pager
	 * @return VidiunMixListResponse Wrapper for array of media entries and total count
	 */
	function listAction(VidiunMixEntryFilter $filter = null, VidiunFilterPager $pager = null)
	{
	    if (!$filter)
			$filter = new VidiunMixEntryFilter();
			
		$filter->typeEqual = VidiunEntryType::MIX; 
	    list($list, $totalCount) = parent::listEntriesByFilter($filter, $pager);
	    
	    $newList = VidiunMixEntryArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunMixListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/**
	* Count mix entries by filter.
	* 
	* @action count
	* @param VidiunMediaEntryFilter $filter Media entry filter
	* @return int
	*/
	function countAction(VidiunMediaEntryFilter $filter = null)
	{
	    if (!$filter)
			$filter = new VidiunMediaEntryFilter();
			
		$filter->typeEqual = VidiunEntryType::MIX;
		
		return parent::countEntriesByFilter($filter);
	}
	
	/**
	 * Clones an existing mix.
	 *
	 * @action clone
	 * @param string $entryId Mix entry id to clone
	 * @return VidiunMixEntry The new mix entry
	 */
	function cloneAction($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);

		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::MIX)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		$vshowId = $dbEntry->getVshowId();
		$vshow = $dbEntry->getVshow();
		
		if (!$vshow)
		{
			VidiunLog::CRIT("Vshow was not found for mix id [".$entryId."]");
			throw new VidiunAPIException(VidiunErrors::INTERNAL_SERVERL_ERROR);
		}
		
		$newVshow = myVshowUtils::shalowCloneById($vshowId, $this->getVuser()->getId());
	
		if (!$newVshow)
		{
			VidiunLog::ERR("Failed to clone vshow for mix id [".$entryId."]");
			throw new VidiunAPIException(VidiunErrors::INTERNAL_SERVERL_ERROR);
		}
		$newEntry = $newVshow->getShowEntry();
		
		$newMixEntry = new VidiunMixEntry();
		$newMixEntry->fromObject($newEntry, $this->getResponseProfile());
		
		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $newEntry);
		
		return $newMixEntry;
	}
	
	/**
	 * Appends a media entry to the end of the mix timeline, this will save the mix timeline as a new version.
	 * 
	 * @action appendMediaEntry
	 * @param string $mixEntryId Mix entry to append to its timeline
	 * @param string $mediaEntryId Media entry to append to the timeline
	 * @return VidiunMixEntry The mix entry
	 */
	function appendMediaEntryAction($mixEntryId, $mediaEntryId)
	{
		$dbMixEntry = entryPeer::retrieveByPK($mixEntryId);

		if (!$dbMixEntry || $dbMixEntry->getType() != VidiunEntryType::MIX)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $mixEntryId);
			
		$dbMediaEntry = entryPeer::retrieveByPK($mediaEntryId);

		if (!$dbMediaEntry || $dbMediaEntry->getType() != VidiunEntryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $mediaEntryId);
			
		$vshow = $dbMixEntry->getvshow();		
		if (!$vshow)
		{
			VidiunLog::CRIT("Vshow was not found for mix id [".$mixEntryId."]");
			throw new VidiunAPIException(VidiunErrors::INTERNAL_SERVERL_ERROR);
		}
		
		// FIXME: temp hack  - when vshow doesn't have a roughcut, and the media entry is not ready, it cannob be queued for append upon import/conversion completion 
		if ($dbMediaEntry->getStatus() != entryStatus::READY)
		{
			$vshow->setShowEntryId($mixEntryId);
			$vshow->save();
			$dbMediaEntry->setVshowId($vshow->getId());
			$dbMediaEntry->save();
		}
		
		$metadata = $vshow->getMetadata();
		
		$relevantVshowVersion = 1 + $vshow->getVersion(); // the next metadata will be the first relevant version for this new entry
		
		$newMetadata = myMetadataUtils::addEntryToMetadata($metadata, $dbMediaEntry, $relevantVshowVersion, array());
		
		$dbMediaEntry->save(); // FIXME: should be removed, needed for the prev hack
		
		if ($newMetadata)
		{
			// TODO - add thumbnail only for entries that are worthy - check they are not moderated !
			$thumbModified = myVshowUtils::updateThumbnail($vshow, $dbMediaEntry, false);
			
			if ($thumbModified)
			{
			    $newMetadata = myMetadataUtils::updateThumbUrlFromMetadata($newMetadata, $dbMixEntry->getThumbnailUrl());
			}
			
			// it is very important to increment the version count because even if the entry is deferred
			// it will be added on the next version
			
			if (!$vshow->getHasRoughcut())
			{
				// make sure the vshow now does have a roughcut
				$vshow->setHasRoughcut(true);	
				$vshow->save();
			}
	
			$vshow->setMetadata($newMetadata, true);
		}
		
		$mixEntry = new VidiunMixEntry();
		$mixEntry->fromObject($dbMixEntry, $this->getResponseProfile());
		
		return $mixEntry;
	}
	
	/**
	 * Get the mixes in which the media entry is included
	 *
	 * @action getMixesByMediaId
	 * @param string $mediaEntryId
	 * @return VidiunMixEntryArray
	 */
	public function getMixesByMediaIdAction($mediaEntryId)
	{
		$dbMediaEntry = entryPeer::retrieveByPK($mediaEntryId);

		if (!$dbMediaEntry || $dbMediaEntry->getType() != VidiunEntryType::MEDIA_CLIP)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $mediaEntryId);
			
		 $list = roughcutEntry::getAllRoughcuts($mediaEntryId);
		 $newList = VidiunMixEntryArray::fromDbArray($list, $this->getResponseProfile());
		 return $newList;
	}
	
	/**
	 * Get all ready media entries that exist in the given mix id
	 *
	 * @action getReadyMediaEntries
	 * @param string $mixId
	 * @param int $version Desired version to get the data from
	 * @return VidiunMediaEntryArray
	 */
	public function getReadyMediaEntriesAction($mixId, $version = -1)
	{
		$dbEntry = entryPeer::retrieveByPK($mixId);

		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::MIX)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $mixId);
		
		$dataSyncKey = $dbEntry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
		$mixFileName = vFileSyncUtils::getReadyLocalFilePathForKey($dataSyncKey, false);
		if(!$mixFileName)
			VExternalErrors::dieError(VExternalErrors::FILE_NOT_FOUND);

		$entryDataFromMix = myFlvStreamer::getAllAssetsData($dataSyncKey);

		$ids = array();
		foreach($entryDataFromMix as $data)
			$ids[] = $data["id"];

		$c = VidiunCriteria::create(entryPeer::OM_CLASS);
		$c->addAnd(entryPeer::ID, $ids, Criteria::IN);
		$c->addAnd(entryPeer::TYPE, entryType::MEDIA_CLIP);					
		
		$dbEntries = entryPeer::doSelect($c);

		$mediaEntries = VidiunMediaEntryArray::fromDbArray($dbEntries, $this->getResponseProfile());
		
		return $mediaEntries;
	}
	
	/**
	 * Anonymously rank a mix entry, no validation is done on duplicate rankings
	 *  
	 * @action anonymousRank
	 * @param string $entryId
	 * @param int $rank
	 */
	public function anonymousRankAction($entryId, $rank)
	{
		return parent::anonymousRankEntry($entryId, VidiunEntryType::MIX, $rank);
	}
}

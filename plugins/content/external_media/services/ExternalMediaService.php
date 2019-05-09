<?php
/**
 * External media service lets you upload and manage embed codes and external playable content
 *
 * @service externalMedia
 * @package plugins.externalMedia
 * @subpackage api.services
 */
class ExternalMediaService extends VidiunEntryService
{
	protected function vidiunNetworkAllowed($actionName)
	{
		if($actionName === 'get')
			return true;
		
		return parent::vidiunNetworkAllowed($actionName);
	}
	
	/**
	 * Add external media entry
	 *
	 * @action add
	 * @param VidiunExternalMediaEntry $entry
	 * @return VidiunExternalMediaEntry
	 */
	function addAction(VidiunExternalMediaEntry $entry)
	{
		$dbEntry = parent::add($entry, $entry->conversionProfileId);
		$dbEntry->setStatus(entryStatus::READY);
		$dbEntry->save();
		
		$trackEntry = new TrackEntry();
		$trackEntry->setEntryId($dbEntry->getId());
		$trackEntry->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY);
		$trackEntry->setDescription(__METHOD__ . ":" . __LINE__ . "::ENTRY_EXTERNAL_MEDIA");
		TrackEntry::addTrackEntry($trackEntry);
		
		$entry->fromObject($dbEntry, $this->getResponseProfile());
		return $entry;
	}
	
	/**
	 * Get external media entry by ID.
	 * 
	 * @action get
	 * @param string $id External media entry id
	 * @return VidiunExternalMediaEntry The requested external media entry
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function getAction($id)
	{
		return $this->getEntry($id, ExternalMediaPlugin::getEntryTypeCoreValue(ExternalMediaEntryType::EXTERNAL_MEDIA));
	}
	
	/**
	 * Update external media entry. Only the properties that were set will be updated.
	 * 
	 * @action update
	 * @param string $id External media entry id to update
	 * @param VidiunExternalMediaEntry $entry External media entry object to update
	 * @return VidiunExternalMediaEntry The updated external media entry
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry id edit
	 */
	function updateAction($id, VidiunExternalMediaEntry $entry)
	{
		return $this->updateEntry($id, $entry, ExternalMediaPlugin::getEntryTypeCoreValue(ExternalMediaEntryType::EXTERNAL_MEDIA));
	}
	
	/**
	 * Delete a external media entry.
	 *
	 * @action delete
	 * @param string $id External media entry id to delete
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry id edit
	 */
	function deleteAction($id)
	{
		$this->deleteEntry($id, ExternalMediaPlugin::getEntryTypeCoreValue(ExternalMediaEntryType::EXTERNAL_MEDIA));
	}
	
	/**
	 * List media entries by filter with paging support.
	 * 
	 * @action list
	 * @param VidiunExternalMediaEntryFilter $filter External media entry filter
	 * @param VidiunFilterPager $pager Pager
	 * @return VidiunExternalMediaEntryListResponse Wrapper for array of media entries and total count
	 */
	function listAction(VidiunExternalMediaEntryFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
			$filter = new VidiunExternalMediaEntryFilter();
		
		list($list, $totalCount) = parent::listEntriesByFilter($filter, $pager);
		
		$response = new VidiunExternalMediaEntryListResponse();
		$response->objects = VidiunExternalMediaEntryArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/**
	 * Count media entries by filter.
	 * 
	 * @action count
	 * @param VidiunExternalMediaEntryFilter $filter External media entry filter
	 * @return int
	 */
	function countAction(VidiunExternalMediaEntryFilter $filter = null)
	{
		if(!$filter)
			$filter = new VidiunExternalMediaEntryFilter();
		
		return parent::countEntriesByFilter($filter);
	}

	protected function duplicateTemplateEntry($conversionProfileId, $templateEntryId, $baseTo = null)
	{
		return parent::duplicateTemplateEntry($conversionProfileId, $templateEntryId, new ExternalMediaEntry());
	}
}

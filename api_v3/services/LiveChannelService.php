<?php

/**
 * Live Channel service lets you manage live channels
 *
 * @service liveChannel
 * @package api
 * @subpackage services
 */
class LiveChannelService extends VidiunLiveEntryService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if($this->getPartnerId() > 0 && !PermissionPeer::isValidForPartner(PermissionName::FEATURE_LIVE_CHANNEL, $this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
	}
	
	/**
	 * Adds new live channel.
	 * 
	 * @action add
	 * @param VidiunLiveChannel $liveChannel Live channel metadata  
	 * @return VidiunLiveChannel The new live channel
	 */
	function addAction(VidiunLiveChannel $liveChannel)
	{
		$dbEntry = $this->prepareEntryForInsert($liveChannel);
		$dbEntry->save();
		
		$te = new TrackEntry();
		$te->setEntryId($dbEntry->getId());
		$te->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY);
		$te->setDescription(__METHOD__ . ":" . __LINE__ . "::LIVE_CHANNEL");
		TrackEntry::addTrackEntry($te);
		
		$liveChannel = new VidiunLiveChannel();
		$liveChannel->fromObject($dbEntry, $this->getResponseProfile());
		return $liveChannel;
	}

	
	/**
	 * Get live channel by ID.
	 * 
	 * @action get
	 * @param string $id Live channel id
	 * @return VidiunLiveChannel The requested live channel
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function getAction($id)
	{
		return $this->getEntry($id, -1, VidiunEntryType::LIVE_CHANNEL);
	}

	
	/**
	 * Update live channel. Only the properties that were set will be updated.
	 * 
	 * @action update
	 * @param string $id Live channel id to update
	 * @param VidiunLiveChannel $liveChannel Live channel metadata to update
	 * @return VidiunLiveChannel The updated live channel
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry id edit
	 */
	function updateAction($id, VidiunLiveChannel $liveChannel)
	{
		return $this->updateEntry($id, $liveChannel, VidiunEntryType::LIVE_CHANNEL);
	}

	/**
	 * Delete a live channel.
	 *
	 * @action delete
	 * @param string $id Live channel id to delete
	 * 
 	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
 	 * @validateUser entry id edit
	 */
	function deleteAction($id)
	{
		$this->deleteEntry($id, VidiunEntryType::LIVE_CHANNEL);
	}
	
	/**
	 * List live channels by filter with paging support.
	 * 
	 * @action list
     * @param VidiunLiveChannelFilter $filter live channel filter
	 * @param VidiunFilterPager $pager Pager
	 * @return VidiunLiveChannelListResponse Wrapper for array of live channels and total count
	 */
	function listAction(VidiunLiveChannelFilter $filter = null, VidiunFilterPager $pager = null)
	{
	    if (!$filter)
			$filter = new VidiunLiveChannelFilter();
			
	    $filter->typeEqual = VidiunEntryType::LIVE_CHANNEL;
	    list($list, $totalCount) = parent::listEntriesByFilter($filter, $pager);
	    
	    $newList = VidiunLiveChannelArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunLiveChannelListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/**
	 * Delivering the status of a live channel (on-air/offline)
	 * 
	 * @action isLive
	 * @param string $id ID of the live channel
	 * @return bool
	 * @vsOptional
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	public function isLiveAction ($id)
	{
		$dbEntry = entryPeer::retrieveByPK($id);

		if (!$dbEntry || $dbEntry->getType() != VidiunEntryType::LIVE_CHANNEL)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $id);

		return $dbEntry->isCurrentlyLive();
	}
}
<?php

/**
 * Manage live channel segments
 *
 * @service liveChannelSegment
 */
class LiveChannelSegmentService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('LiveChannelSegment'); 	
		
		if(!PermissionPeer::isValidForPartner(PermissionName::FEATURE_LIVE_CHANNEL, $this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
	}
	
	/**
	 * Add new live channel segment
	 * 
	 * @action add
	 * @param VidiunLiveChannelSegment $liveChannelSegment
	 * @return VidiunLiveChannelSegment
	 */
	function addAction(VidiunLiveChannelSegment $liveChannelSegment)
	{
		$dbLiveChannelSegment = $liveChannelSegment->toInsertableObject();
		$dbLiveChannelSegment->setPartnerId($this->getPartnerId());
		$dbLiveChannelSegment->setStatus(LiveChannelSegmentStatus::ACTIVE);
		$dbLiveChannelSegment->save();
		
		$liveChannelSegment = new VidiunLiveChannelSegment();
		$liveChannelSegment->fromObject($dbLiveChannelSegment, $this->getResponseProfile());
		return $liveChannelSegment;
	}
	
	/**
	 * Get live channel segment by id
	 * 
	 * @action get
	 * @param bigint $id
	 * @return VidiunLiveChannelSegment
	 * 
	 * @throws VidiunErrors::LIVE_CHANNEL_SEGMENT_ID_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbLiveChannelSegment = LiveChannelSegmentPeer::retrieveByPK($id);
		if (!$dbLiveChannelSegment)
			throw new VidiunAPIException(VidiunErrors::LIVE_CHANNEL_SEGMENT_ID_NOT_FOUND, $id);
			
		$liveChannelSegment = new VidiunLiveChannelSegment();
		$liveChannelSegment->fromObject($dbLiveChannelSegment, $this->getResponseProfile());
		return $liveChannelSegment;
	}
	
	/**
	 * Update live channel segment by id
	 * 
	 * @action update
	 * @param bigint $id
	 * @param VidiunLiveChannelSegment $liveChannelSegment
	 * @return VidiunLiveChannelSegment
	 * 
	 * @throws VidiunErrors::LIVE_CHANNEL_SEGMENT_ID_NOT_FOUND
	 */
	function updateAction($id, VidiunLiveChannelSegment $liveChannelSegment)
	{
		$dbLiveChannelSegment = LiveChannelSegmentPeer::retrieveByPK($id);
		if (!$dbLiveChannelSegment)
			throw new VidiunAPIException(VidiunErrors::LIVE_CHANNEL_SEGMENT_ID_NOT_FOUND, $id);
		
		$liveChannelSegment->toUpdatableObject($dbLiveChannelSegment);
		$dbLiveChannelSegment->save();
		
		$liveChannelSegment = new VidiunLiveChannelSegment();
		$liveChannelSegment->fromObject($dbLiveChannelSegment, $this->getResponseProfile());
		return $liveChannelSegment;
	}
	
	/**
	 * Delete live channel segment by id
	 * 
	 * @action delete
	 * @param bigint $id
	 * 
	 * @throws VidiunErrors::LIVE_CHANNEL_SEGMENT_ID_NOT_FOUND
	 */
	function deleteAction($id)
	{
		$dbLiveChannelSegment = LiveChannelSegmentPeer::retrieveByPK($id);
		if (!$dbLiveChannelSegment)
			throw new VidiunAPIException(VidiunErrors::LIVE_CHANNEL_SEGMENT_ID_NOT_FOUND, $id);

		$dbLiveChannelSegment->setStatus(LiveChannelSegmentStatus::DELETED);
		$dbLiveChannelSegment->save();
	}
	
	/**
	 * List live channel segments by filter and pager
	 * 
	 * @action list
	 * @param VidiunFilterPager $filter
	 * @param VidiunLiveChannelSegmentFilter $pager
	 * @return VidiunLiveChannelSegmentListResponse
	 */
	function listAction(VidiunLiveChannelSegmentFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunLiveChannelSegmentFilter();
			
		if (!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
}
<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunLiveChannelSegmentFilter extends VidiunLiveChannelSegmentBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new LiveChannelSegmentFilter();
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$liveChannelSegmentFilter = $this->toObject();

		$c = new Criteria();
		$liveChannelSegmentFilter->attachToCriteria($c);
		
		$totalCount = LiveChannelSegmentPeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = LiveChannelSegmentPeer::doSelect($c);
		
		$list = VidiunLiveChannelSegmentArray::fromDbArray($dbList, $responseProfile);
		$response = new VidiunLiveChannelSegmentListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;    
	}
}

<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunLiveChannelFilter extends VidiunLiveChannelBaseFilter
{
	public function __construct()
	{
		$this->typeIn = VidiunEntryType::LIVE_CHANNEL;
	}

	/* (non-PHPdoc)
	 * @see VidiunBaseEntryFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager);
		
	    $newList = VidiunLiveChannelArray::fromDbArray($list, $responseProfile);
		$response = new VidiunLiveChannelListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}

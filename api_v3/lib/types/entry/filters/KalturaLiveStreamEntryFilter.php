<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunLiveStreamEntryFilter extends VidiunLiveStreamEntryBaseFilter
{
	public function __construct()
	{
		$this->typeIn = VidiunEntryType::LIVE_STREAM;
	}

	/* (non-PHPdoc)
	 * @see VidiunBaseEntryFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager);
		
	    $newList = VidiunLiveStreamEntryArray::fromDbArray($list, $responseProfile);
		$response = new VidiunBaseEntryListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}

<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunDataEntryFilter extends VidiunDataEntryBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunBaseEntryFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager);
		
	    $newList = VidiunDataEntryArray::fromDbArray($list, $responseProfile);
		$response = new VidiunBaseEntryListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}

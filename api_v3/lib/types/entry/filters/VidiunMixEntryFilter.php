<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunMixEntryFilter extends VidiunMixEntryBaseFilter
{
	public function __construct()
	{
		$this->typeIn = VidiunEntryType::MIX;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseEntryFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager);
		
	    $newList = VidiunMixEntryArray::fromDbArray($list, $responseProfile);
		$response = new VidiunBaseEntryListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}

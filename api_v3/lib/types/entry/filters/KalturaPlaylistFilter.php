<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunPlaylistFilter extends VidiunPlaylistBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunBaseEntryFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager);
		
	    $newList = VidiunPlaylistArray::fromDbArray($list, $responseProfile);
		$response = new VidiunPlaylistListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}

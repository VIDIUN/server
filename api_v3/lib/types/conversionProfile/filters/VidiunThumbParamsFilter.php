<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunThumbParamsFilter extends VidiunThumbParamsBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunAssetParamsFilter::getTypeListResponse()
	 */
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager, $types);
		
		$response = new VidiunThumbParamsListResponse();
		$response->objects = VidiunThumbParamsArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;  
	}
}

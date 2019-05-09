<?php
/**
 * @package plugins.caption
 * @subpackage api.filters
 */
class VidiunCaptionParamsFilter extends VidiunCaptionParamsBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunAssetParamsFilter::getTypeListResponse()
	 */
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager, $types);
		
		$response = new VidiunCaptionParamsListResponse();
		$response->objects = VidiunCaptionParamsArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;  
	}
}

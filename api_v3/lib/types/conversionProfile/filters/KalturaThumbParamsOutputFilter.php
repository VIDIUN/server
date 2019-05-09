<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunThumbParamsOutputFilter extends VidiunThumbParamsOutputBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new assetParamsOutputFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunAssetParamsFilter::getTypeListResponse()
	 */
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager, $types);
		
		$response = new VidiunThumbParamsOutputListResponse();
		$response->objects = VidiunThumbParamsOutputArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;  
	}
}

<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunFlavorParamsOutputFilter extends VidiunFlavorParamsOutputBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new assetParamsOutputFilter();
	}
	
	protected function doGetListResponse(VidiunFilterPager $pager, array $types = null)
	{
		$flavorParamsOutputFilter = $this->toObject();
	
		$c = new Criteria();
		$flavorParamsOutputFilter->attachToCriteria($c);
	
		$pager->attachToCriteria($c);
	
		if($types)
		{
			$c->add(assetParamsOutputPeer::TYPE, $types, Criteria::IN);
		}
	
		$list = assetParamsOutputPeer::doSelect($c);
	
		$c->setLimit(null);
		$totalCount = assetParamsOutputPeer::doCount($c);
	
		return array($list, $totalCount);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunAssetParamsFilter::getTypeListResponse()
	 */
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager, $types);
		
		$response = new VidiunFlavorParamsOutputListResponse();
		$response->objects = VidiunFlavorParamsOutputArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;  
	}
}

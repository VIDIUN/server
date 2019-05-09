<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunAssetParamsFilter extends VidiunAssetParamsBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new assetParamsFilter();
	}

	protected function doGetListResponse(VidiunFilterPager $pager, array $types = null)
	{
		$flavorParamsFilter = $this->toObject();
		
		$c = new Criteria();
		$flavorParamsFilter->attachToCriteria($c);
		
		$pager->attachToCriteria($c);
		
		if($types)
		{
			$c->add(assetParamsPeer::TYPE, $types, Criteria::IN);
		}
		
		$list = assetParamsPeer::doSelect($c);
		
		$c->setLimit(null);
		$totalCount = assetParamsPeer::doCount($c);

		return array($list, $totalCount);
	}

	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager, $types);
		
		$response = new VidiunFlavorParamsListResponse();
		$response->objects = VidiunFlavorParamsArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;  
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		return $this->getTypeListResponse($pager, $responseProfile);  
	}
}

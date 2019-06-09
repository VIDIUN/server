<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunUserLoginDataFilter extends VidiunUserLoginDataBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new UserLoginDataFilter();
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{	
		$userLoginDataFilter = $this->toObject();
		
		$c = new Criteria();
		$userLoginDataFilter->attachToCriteria($c);
		
		$totalCount = UserLoginDataPeer::doCount($c);
		$pager->attachToCriteria($c);
		$list = UserLoginDataPeer::doSelect($c);
		$newList = VidiunUserLoginDataArray::fromDbArray($list, $responseProfile);
		
		$response = new VidiunUserLoginDataListResponse();
		$response->totalCount = $totalCount;
		$response->objects = $newList;
		return $response;
	}
}

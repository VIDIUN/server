<?php
/**
 * @package plugins.reach
 * @subpackage api.filters
 */
class VidiunReachProfileFilter extends VidiunReachProfileBaseFilter
{
	protected function getCoreFilter()
	{
		return new ReachProfileFilter();
	}
	
	/* (non-PHPdoc)
 	 * @see VidiunRelatedFilter::getListResponse()
 	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$c = new Criteria();
		$filter = $this->toObject();
		$filter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		
		$list = ReachProfilePeer::doSelect($c);
		
		$resultCount = count($list);
		if ($resultCount && $resultCount < $pager->pageSize)
			$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
		else
		{
			VidiunFilterPager::detachFromCriteria($c);
			$totalCount = ReachProfilePeer::doCount($c);
		}
		
		$response = new VidiunReachProfileListResponse();
		$response->objects = VidiunReachProfileArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;
	}
}

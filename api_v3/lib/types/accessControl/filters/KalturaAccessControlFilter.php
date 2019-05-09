<?php
/**
 * @package api
 * @subpackage filters
 * @deprecated use VidiunAccessControlProfileFilter instead
 */
class VidiunAccessControlFilter extends VidiunAccessControlBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new accessControlFilter();
	}

	/* (non-PHPdoc)
	 * @see VidiunFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$accessControlFilter = $this->toObject();

		$c = new Criteria();
		$accessControlFilter->attachToCriteria($c);
		
		$totalCount = accessControlPeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = accessControlPeer::doSelect($c);
		
		$list = VidiunAccessControlArray::fromDbArray($dbList, $responseProfile);
		$response = new VidiunAccessControlListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response; 
	}
}

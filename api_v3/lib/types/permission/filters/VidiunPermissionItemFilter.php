<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunPermissionItemFilter extends VidiunPermissionItemBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new PermissionItemFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$permissionItemFilter = $this->toObject();
		
		$c = new Criteria();
		$permissionItemFilter->attachToCriteria($c);
		$count = PermissionItemPeer::doCount($c);
		
		$pager->attachToCriteria ( $c );
		$list = PermissionItemPeer::doSelect($c);
		
		$response = new VidiunPermissionItemListResponse();
		$response->objects = VidiunPermissionItemArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $count;
		
		return $response;
	}
}

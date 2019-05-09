<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunPermissionFilter extends VidiunPermissionBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new PermissionFilter();
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$permissionFilter = $this->toObject();
		
		$c = new Criteria();
		$permissionFilter->attachToCriteria($c);
		$count = PermissionPeer::doCount($c);
		
		$pager->attachToCriteria ( $c );
		
		$list = PermissionPeer::doSelect($c);
		
		$response = new VidiunPermissionListResponse();
		$response->objects = VidiunPermissionArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $count;
		
		return $response;
	}
}

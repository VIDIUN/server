<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunUserRoleFilter extends VidiunUserRoleBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new UserRoleFilter();
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$userRoleFilter = $this->toObject();

		$c = new Criteria();
		$userRoleFilter->attachToCriteria($c);
		$count = UserRolePeer::doCount($c);
		
		$pager->attachToCriteria ( $c );
		$list = UserRolePeer::doSelect($c);
		
		$response = new VidiunUserRoleListResponse();
		$response->objects = VidiunUserRoleArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $count;
		
		return $response;
	}
}

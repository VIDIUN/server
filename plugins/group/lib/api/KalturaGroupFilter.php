<?php
/**
 * @package plugins.group
 * @subpackage api.filters
 */
class VidiunGroupFilter extends VidiunUserFilter
{
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$c = VidiunCriteria::create(vuserPeer::OM_CLASS);
		$groupFilter = $this->toObject();
		$groupFilter->attachToCriteria($c);
		$c->addAnd(vuserPeer::TYPE,VuserType::GROUP);
		$c->addAnd(vuserPeer::PUSER_ID, NULL, VidiunCriteria::ISNOTNULL);
		$pager->attachToCriteria($c);
		$list = vuserPeer::doSelect($c);
		$totalCount = $c->getRecordsCount();
		$newList = VidiunGroupArray::fromDbArray($list, $responseProfile);
		$response = new VidiunGroupListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
}
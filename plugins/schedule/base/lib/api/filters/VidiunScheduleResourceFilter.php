<?php
/**
 * @package plugins.schedule
 * @subpackage api.filters
 */
class VidiunScheduleResourceFilter extends VidiunScheduleResourceBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new ScheduleResourceFilter();
	}
	
	protected function getListResponseType()
	{
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$type = $this->getListResponseType();

		if(!isset($this->statusEqual) && !isset($this->statusIn))
		{
			$allowedStatus = array(ScheduleResourceStatus::DISABLED,ScheduleResourceStatus::ACTIVE);
			$this->statusIn =  implode(',' , $allowedStatus);
		}

		$c = new Criteria();
		if($type)
		{
			$c->add(ScheduleResourcePeer::TYPE, $type);
		}

		$filter = $this->toObject();
		$filter->attachToCriteria($c);
		$pager->attachToCriteria($c);

		$list = ScheduleResourcePeer::doSelect($c);
		$resultCount = count($list);
		if ($resultCount && $resultCount < $pager->pageSize)
			$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
		else
		{
			VidiunFilterPager::detachFromCriteria($c);
			$totalCount = ScheduleResourcePeer::doCount($c);
		}
		
		$response = new VidiunScheduleResourceListResponse();
		$response->objects = VidiunScheduleResourceArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;
	}
}

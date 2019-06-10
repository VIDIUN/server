<?php
/**
 * @package plugins.schedule
 * @subpackage api.filters
 */
class VidiunScheduleEventResourceFilter extends VidiunScheduleEventResourceBaseFilter
{
	/**
	 * Find event-resource objects that associated with the event, if none found, find by its parent event
	 * @var int
	 */
	public $eventIdOrItsParentIdEqual;

	static private $map_between_objects = array
	(
			"eventIdOrItsParentIdEqual" => "_eq_event_id_or_parent",
	);
	
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new ScheduleEventResourceFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null,
									$filterBlackoutConflicts = true)
	{
		$c = new Criteria();
		$filter = $this->toObject();
		$filter->attachToCriteria($c);
		$pager->attachToCriteria($c);
			
		$list = ScheduleEventResourcePeer::doSelect($c);
		$resultCount = count($list);
		if ($resultCount && $resultCount < $pager->pageSize)
		{
			$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
		}
		else
		{
			VidiunFilterPager::detachFromCriteria($c);
			$totalCount = ScheduleEventResourcePeer::doCount($c);
		}

		if($filterBlackoutConflicts)
		{
			$list = array_filter($list, array($this, "checkNoBlackoutConflict"));
		}

		$response = new VidiunScheduleEventResourceListResponse();
		$response->objects = VidiunScheduleEventResourceArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;
	}

	/**
	 * @param baseScheduleResource $baseScheduleResource
	 * @return bool
	 */
	public function checkNoBlackoutConflict($baseScheduleResource)
	{
		$scheduleEvent = BaseScheduleEventPeer::retrieveByPK($baseScheduleResource->getEventId());
		return !($scheduleEvent && $scheduleEvent->getBlackoutConflicts());
	}
}

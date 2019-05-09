<?php
/**
 * @package plugins.schedule
 * @subpackage api.filters
 */
class VidiunScheduleEventFilter extends VidiunScheduleEventBaseFilter
{
	static private $map_between_objects = array
	(
		"resourceIdsLike" => "_like_resource_ids",
		"resourceIdsMultiLikeOr" => "_mlikeor_resource_ids",
		"resourceIdsMultiLikeAnd" => "_mlikeand_resource_ids",
		"parentResourceIdsLike" => "_like_parent_resource_ids",
		"parentResourceIdsMultiLikeOr" => "_mlikeor_parent_resource_ids",
		"parentResourceIdsMultiLikeAnd" => "_mlikeand_parent_resource_ids",
		"templateEntryCategoriesIdsLike" => "_like_template_entry_categories_ids",
		"templateEntryCategoriesIdsMultiLikeAnd" => "_mlikeand_template_entry_categories_ids",
		"templateEntryCategoriesIdsMultiLikeOr" => "_mlikeor_template_entry_categories_ids",
		"resourceSystemNamesLike" => "_like_resource_system_names",
		"resourceSystemNamesMultiLikeOr" => "_mlikeor_resource_system_names",
		"resourceSystemNamesMultiLikeAnd" => "_mlikeand_resource_system_names",
		"resourceIdEqual" => "_eq_resource_ids",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/**
	 * @var string
	 */
	public $resourceIdsLike;

	/**
	 * @var string
	 */
	public $resourceIdsMultiLikeOr;

	/**
	 * @var string
	 */
	public $resourceIdsMultiLikeAnd;

	/**
	 * @var string
	 */
	public $parentResourceIdsLike;

	/**
	 * @var string
	 */
	public $parentResourceIdsMultiLikeOr;

	/**
	 * @var string
	 */
	public $parentResourceIdsMultiLikeAnd;

	/**
	 * @var string
	 */
	public $templateEntryCategoriesIdsMultiLikeAnd;

	/**
	 * @var string
	 */

	public $templateEntryCategoriesIdsMultiLikeOr;

	/**
	 * @var string
	 */
	public $resourceSystemNamesMultiLikeOr;

	/**
	 * @var string
	 */
	public $templateEntryCategoriesIdsLike;

	/**
	 * @var string
	 */
	public $resourceSystemNamesMultiLikeAnd;

	/**
	 * @var string
	 */
	public $resourceSystemNamesLike;

	/**
	 * @var string
	 */
	public $resourceIdEqual;

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new ScheduleEventFilter();
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

		if ($this->ownerIdEqual)
		{
			$dbVuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::$vs_partner_id, $this->ownerIdEqual);
			if (!$dbVuser)
			{
				throw new VidiunAPIException (VidiunErrors::INVALID_USER_ID);
			}
			$this->ownerIdEqual = $dbVuser->getId();
		}
		if ($this->ownerIdIn)
		{
			$userIds = explode(",", $this->ownerIdIn);
			$dbVusers = vuserPeer::getVuserByPartnerAndUids(vCurrentContext::$vs_partner_id, $userIds);
			if (count($dbVusers) < count($userIds))
			{
				throw new VidiunAPIException (VidiunErrors::INVALID_USER_ID);
			}
			$vuserIds = array();
			foreach ($dbVusers as $dbVuser)
			{
				$vuserIds[] = $dbVuser->getId();
			}

			$this->ownerIdIn = implode(',', $vuserIds);
		}

		$c = VidiunCriteria::create(ScheduleEventPeer::OM_CLASS);
		if ($type)
		{
			$c->add(ScheduleEventPeer::TYPE, $type);
		}

		$filter = $this->toObject();
		$filter->attachToCriteria($c);
		$pager->attachToCriteria($c);

		$eventsList = ScheduleEventPeer::doSelect($c);
		if(count($eventsList) && $type != ScheduleEventType::BLACKOUT)
		{
			$this->loadSessionBlackoutEvents($eventsList);
		}

		$response = new VidiunScheduleEventListResponse();
		$response->objects = VidiunScheduleEventArray::fromDbArray($eventsList, $responseProfile);
		$response->totalCount = $c->getRecordsCount();
		return $response;
	}

	protected function loadSessionBlackoutEvents($eventsList)
	{
		$startDate = PHP_INT_MAX;
		$endDate = 0;
		foreach ($eventsList as $event)
		{
			$eventStartDate = $event->getStartDate('U');
			$eventEndDate = $event->getEndDate('U');
			if ($eventStartDate < $startDate)
			{
				$startDate = $eventStartDate;
			}

			if ($eventEndDate > $endDate)
			{
				$endDate = $eventEndDate;
			}
		}

		ScheduleEventPeer::retrieveBlackoutEventsByDateWindow($startDate, $endDate);
	}
}

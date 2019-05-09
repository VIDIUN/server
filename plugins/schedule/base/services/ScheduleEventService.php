<?php

/**
 * The ScheduleEvent service enables you to create and manage (update, delete, retrieve, etc.) scheduled recording events.
 * @service scheduleEvent
 * @package plugins.schedule
 * @subpackage api.services
 */
class ScheduleEventService extends VidiunBaseService
{
	/* (non-PHPdoc)
	 * @see VidiunBaseService::initService()
	 */
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		$this->applyPartnerFilterForClass('ScheduleEvent');
		$this->applyPartnerFilterForClass('ScheduleResource');
		$this->applyPartnerFilterForClass('ScheduleEventResource');
	}

	/**
	 * Allows you to add a new VidiunScheduleEvent object
	 *
	 * @action add
	 * @param VidiunScheduleEvent $scheduleEvent
	 * @return VidiunScheduleEvent
	 */
	public function addAction(VidiunScheduleEvent $scheduleEvent)
	{
		// save in database
		$dbScheduleEvent = $scheduleEvent->toInsertableObject();
		/* @var $dbScheduleEvent ScheduleEvent */

		$dates = null;

		if($dbScheduleEvent->getRecurrenceType() === ScheduleEventRecurrenceType::RECURRING)
			$this->createRecurrences($dbScheduleEvent);
		else
			$dbScheduleEvent->save();

		// return the saved object
		$scheduleEvent = VidiunScheduleEvent::getInstance($dbScheduleEvent, $this->getResponseProfile());
		$scheduleEvent->fromObject($dbScheduleEvent, $this->getResponseProfile());
		return $scheduleEvent;

	}

	/**
	 * Retrieve a VidiunScheduleEvent object by ID
	 *
	 * @action get
	 * @param int $scheduleEventId
	 * @return VidiunScheduleEvent
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function getAction($scheduleEventId)
	{
		$dbScheduleEvent = ScheduleEventPeer::retrieveByPK($scheduleEventId);
		if(!$dbScheduleEvent)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $scheduleEventId);
		}

		$scheduleEvent = VidiunScheduleEvent::getInstance($dbScheduleEvent, $this->getResponseProfile());
		$scheduleEvent->fromObject($dbScheduleEvent, $this->getResponseProfile());

		return $scheduleEvent;
	}

	/**
	 * Update an existing VidiunScheduleEvent object
	 *
	 * @action update
	 * @param int $scheduleEventId
	 * @param VidiunScheduleEvent $scheduleEvent
	 * @return VidiunScheduleEvent
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function updateAction($scheduleEventId, VidiunScheduleEvent $scheduleEvent)
	{
		$dbScheduleEvent = ScheduleEventPeer::retrieveByPK($scheduleEventId);
		if(!$dbScheduleEvent)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $scheduleEventId);
		}

		$currentScheduleEventRecurrenceType = $dbScheduleEvent->getRecurrenceType();

		$dbScheduleEvent = $scheduleEvent->toUpdatableObject($dbScheduleEvent);
		/* @var $dbScheduleEvent ScheduleEvent */

		// In case we update a recurring event to be a single event we need to delete all recurrences and set the sequence to 1
		if($dbScheduleEvent->getRecurrenceType() === ScheduleEventRecurrenceType::NONE && $currentScheduleEventRecurrenceType === ScheduleEventRecurrenceType::RECURRING )
		{
			ScheduleEventPeer::deleteByParentId($dbScheduleEvent->getId());
			$dbScheduleEvent->deleteRecurrence();
			$dbScheduleEvent->setSequence(1);
		}

		if($dbScheduleEvent->getRecurrenceType() === ScheduleEventRecurrenceType::RECURRING && $this->shouldUpdateRecurrences($scheduleEvent))
			$this->updateRecurrences($dbScheduleEvent);
		else
			$dbScheduleEvent->save();

		$scheduleEvent = VidiunScheduleEvent::getInstance($dbScheduleEvent, $this->getResponseProfile());
		$scheduleEvent->fromObject($dbScheduleEvent, $this->getResponseProfile());

		return $scheduleEvent;
	}

	/**
	 * Create new schedule recurrences for recurring event
	 *
	 * @param ScheduleEvent $dbScheduleEvent
	 */
	private function createRecurrences(ScheduleEvent $dbScheduleEvent)
	{
		$dates = $this->getRecurrencesDates($dbScheduleEvent);
		self::setRecurringDates($dates, $dbScheduleEvent);
		$class = get_class($dbScheduleEvent);

		if ($dates)
			foreach($dates as $date)
				$this->createRecurrence($class, $dbScheduleEvent->getId(), $date, $dbScheduleEvent->getDuration());
	}

	/**
	 * update schedule recurrences for recurring event
	 * delete redundant recurrences and create new if needed
	 *
	 * @param ScheduleEvent $dbScheduleEvent
	 */
	private function updateRecurrences(ScheduleEvent $dbScheduleEvent)
	{
		$newDates = $this->getRecurrencesDates($dbScheduleEvent);
		self::setRecurringDates($newDates, $dbScheduleEvent);
		if (is_null($newDates) || empty($newDates))
		{
			VidiunLog::debug("No dates have been received - deleting old recurrences");
			ScheduleEventPeer::deleteByParentId($dbScheduleEvent->getId());
			return;
		}

		$ends = $this->getEndDates($newDates, $dbScheduleEvent->getDuration());
		//get all the recurrences that wasn't changed
		$existingScheduleEvents = ScheduleEventPeer::retrieveByParentIdAndStartAndEndDates($dbScheduleEvent->getId(), $newDates , $ends);

		$existingScheduleEventIds = ScheduleEvent::getEventValues($existingScheduleEvents, 'getId');
		$existingScheduleEventStartDates = ScheduleEvent::getEventValues($existingScheduleEvents, 'getStartDate');
		// delete all old recurrences except the one's that hadn't changed
		VidiunLog::debug("Deleting old recurrences except for ids: " . print_r($existingScheduleEventIds, true));
		ScheduleEventPeer::deleteByParentId($dbScheduleEvent->getId(), $existingScheduleEventIds);

		//create only the new/changed ones
		$dates = array_diff($newDates, $existingScheduleEventStartDates);
		VidiunLog::debug("Adding " .count($dates) . " new recurrences");

		$class = get_class($dbScheduleEvent);
		foreach($dates as $date)
			$this->createRecurrence($class, $dbScheduleEvent->getId(), $date, $dbScheduleEvent->getDuration());
	}


	/**
	 * Mark the VidiunScheduleEvent object as deleted
	 *
	 * @action delete
	 * @param int $scheduleEventId
	 * @return VidiunScheduleEvent
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @throws VidiunScheduleErrors::RECURRENCE_CANT_BE_DELETE
	 */
	public function deleteAction($scheduleEventId)
	{
		$dbScheduleEvent = ScheduleEventPeer::retrieveByPK($scheduleEventId);
		if(!$dbScheduleEvent)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $scheduleEventId);
		}

		if($dbScheduleEvent->getRecurrenceType() == ScheduleEventRecurrenceType::RECURRENCE)
		{
			throw new VidiunAPIException(VidiunScheduleErrors::RECURRENCE_CANT_BE_DELETE, $scheduleEventId, $dbScheduleEvent->getParentId());
		}

		$dbScheduleEvent->setStatus(ScheduleEventStatus::DELETED);
		$dbScheduleEvent->save();

		if($dbScheduleEvent->getRecurrenceType() == ScheduleEventRecurrenceType::RECURRING)
		{
			ScheduleEventPeer::deleteByParentId($scheduleEventId);
		}

		$scheduleEvent = VidiunScheduleEvent::getInstance($dbScheduleEvent, $this->getResponseProfile());
		$scheduleEvent->fromObject($dbScheduleEvent, $this->getResponseProfile());

		return $scheduleEvent;
	}

	/**
	 * Mark the VidiunScheduleEvent object as cancelled
	 *
	 * @action cancel
	 * @param int $scheduleEventId
	 * @return VidiunScheduleEvent
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function cancelAction($scheduleEventId)
	{
		$dbScheduleEvent = ScheduleEventPeer::retrieveByPK($scheduleEventId);
		if(!$dbScheduleEvent)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $scheduleEventId);
		}

		$dbScheduleEvent->setStatus(ScheduleEventStatus::CANCELLED);
		$dbScheduleEvent->save();

		if($dbScheduleEvent->getRecurrenceType() == ScheduleEventRecurrenceType::RECURRING)
		{
			ScheduleEventPeer::deleteByParentId($scheduleEventId);
		}

		$scheduleEvent = VidiunScheduleEvent::getInstance($dbScheduleEvent, $this->getResponseProfile());
		$scheduleEvent->fromObject($dbScheduleEvent, $this->getResponseProfile());

		return $scheduleEvent;
	}

	/**
	 * List VidiunScheduleEvent objects
	 *
	 * @action list
	 * @param VidiunScheduleEventFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunScheduleEventListResponse
	 */
	public function listAction(VidiunScheduleEventFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
		{
			$filter = new VidiunScheduleEventFilter();
		}

		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}

		return $filter->getListResponse($pager, $this->getResponseProfile());
	}


	/**
	 * get schedule recurrences dates
	 * @param ScheduleEvent $dbScheduleEvent
	 * @return array unix timestamp
	 */
	private function getRecurrencesDates(ScheduleEvent $dbScheduleEvent)
	{
		$maxRecurrences = SchedulePlugin::getScheduleEventmaxRecurrences();
		$datesGenerator = new DatesGenerator($maxRecurrences, $dbScheduleEvent->getRecurrence()->asArray(), array('VidiunLog', 'debug'));
		$dates = $datesGenerator->getDates($dbScheduleEvent->getStartDate(null));

		VidiunLog::debug("Found [" . count($dates) . "] dates");
		return $dates;
	}

	private function shouldUpdateRecurrences(VidiunScheduleEvent $scheduleEvent)
	{
		$timeRelatedFields = array($scheduleEvent->startDate, $scheduleEvent->endDate, $scheduleEvent->recurrence,
			$scheduleEvent->recurrenceType, $scheduleEvent->duration);
		foreach ($timeRelatedFields as $val)
			if ($val)
				return true;
		return false;
	}
	
	private function getEndDates($startTimes, $duration)
	{
		$ends = array();
		foreach($startTimes as $start)
			$ends[] = $start + $duration;
		return $ends;
	}

	private function createRecurrence($class, $recurringScheduleEventId, $date, $duration)
	{
		$scheduleEvent = new $class();
		$scheduleEvent->setRecurrenceType(ScheduleEventRecurrenceType::RECURRENCE);
		$scheduleEvent->setParentId($recurringScheduleEventId);
		$scheduleEvent->setStartDate($date);
		$scheduleEvent->setOriginalStartDate($date);
		$scheduleEvent->setEndDate($date + $duration);
		$scheduleEvent->save();
	}



	
	/**
	 * @param $dates array
	 * @param $dbScheduleEvent ScheduleEvent
	 */
	private static function setRecurringDates($dates, $dbScheduleEvent)
	{
		if (!is_null($dates) && !empty($dates))
		{
			$dbScheduleEvent->setStartDate($dates[0]);
			$dbScheduleEvent->setEndDate($dates[0] + $dbScheduleEvent->getDuration());
		}
		$dbScheduleEvent->save();
	}

	/**
	 * List conflicting events for resourcesIds by event's dates
	 * @action getConflicts
	 * @param string $resourceIds comma separated
	 * @param VidiunScheduleEvent $scheduleEvent
	 * @param string $scheduleEventIdToIgnore
	 * @param VidiunScheduleEventConflictType $scheduleEventConflictType
	 * @return VidiunScheduleEventListResponse
	 * @throws VidiunAPIException
	 */
	public function getConflictsAction($resourceIds, VidiunScheduleEvent $scheduleEvent, $scheduleEventIdToIgnore = null,
									   $scheduleEventConflictType = VidiunScheduleEventConflictType::RESOURCE_CONFLICT)
	{
		if (!$resourceIds)
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, 'resourceIds');
		}

		/* @var $dbScheduleEvent ScheduleEvent */
		$dbScheduleEvent = $scheduleEvent->toObject();
		$events = array();
		$dates = array();
		if($dbScheduleEvent->getRecurrenceType() === ScheduleEventRecurrenceType::RECURRING)
		{
			$maxRecurrences = SchedulePlugin::getScheduleEventmaxRecurrences();
			$datesGenerator = new DatesGenerator($maxRecurrences, $dbScheduleEvent->getRecurrence()->asArray(), array('VidiunLog', 'debug'));
			$dates = $datesGenerator->getDates($dbScheduleEvent->getStartDate(null));
			$duration = $dbScheduleEvent->getDuration();
		}
		else
		{
			$dates[] = $dbScheduleEvent->getStartDate(null);
			$duration = $dbScheduleEvent->getEndDate(null) - $dbScheduleEvent->getStartDate(null);
		}

		foreach($dates as $date)
		{
			if($scheduleEventConflictType == VidiunScheduleEventConflictType::RESOURCE_CONFLICT ||
				$scheduleEventConflictType == VidiunScheduleEventConflictType::BOTH )
			{
				$events = array_merge($events, ScheduleEventPeer::retrieveEventsByResourceIdsAndDateWindow($resourceIds,
					$date, $date + $duration, $scheduleEventIdToIgnore));
			}

			if($scheduleEventConflictType == VidiunScheduleEventConflictType::BLACKOUT_CONFLICT ||
				$scheduleEventConflictType == VidiunScheduleEventConflictType::BOTH )
			{
				$events = array_merge($events, ScheduleEventPeer::retrieveBlackoutEventsByDateWindow($date,
					$date + $duration, $scheduleEventIdToIgnore));
			}
		}

		if (!count($events))
		{
			$this->reserveResources($resourceIds);
		}

		$response = new VidiunScheduleEventListResponse();
		$response->objects = VidiunScheduleEventArray::fromDbArray($events, $this->getResponseProfile());
		$response->totalCount = count($events);
		return $response;
	}

	private function reserveResources($resourceIds)
	{
		$resourceIdsArray = explode(",", $resourceIds);
		$resourceReservator = new vResourceReservation();
		foreach($resourceIdsArray as $resourceId)
		{
			if (!$resourceReservator->reserve($resourceId))
			{
				VidiunLog::info("Could not reserve all resource id [$resourceId]");
				$this->clearAllReservation($resourceReservator, $resourceIdsArray);
				throw new VidiunAPIException(VidiunErrors::RESOURCE_IS_RESERVED, $resourceId);
			}
		}
	}

	private function clearAllReservation($resourceReservator, $resourceIds)
	{
		/* @var vResourceReservation $resourceReservator*/
		foreach($resourceIds as $resourceId)
			$resourceReservator->deleteReservation($resourceId);
	}
}

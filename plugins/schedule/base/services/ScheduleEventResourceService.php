<?php

/**
 * The ScheduleEventResource service enables you create and manage (update, delete, retrieve, etc.) the connections between recording events and the resources required for these events (cameras, capture devices, etc.).
 * @service scheduleEventResource
 * @package plugins.schedule
 * @subpackage api.services
 */
class ScheduleEventResourceService extends VidiunBaseService
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
	 * Allows you to add a new VidiunScheduleEventResource object
	 * 
	 * @action add
	 * @param VidiunScheduleEventResource $scheduleEventResource
	 * @return VidiunScheduleEventResource
	 */
	public function addAction(VidiunScheduleEventResource $scheduleEventResource)
	{
		$resourceReservator = new vResourceReservation();
		if (!$resourceReservator->checkAvailable($scheduleEventResource->resourceId))
			throw new VidiunAPIException(VidiunErrors::RESOURCE_IS_RESERVED, $scheduleEventResource->resourceId);
		$resourceReservator->reserve($scheduleEventResource->resourceId);
		
		// save in database
		$dbScheduleEventResource = $scheduleEventResource->toInsertableObject();
		$dbScheduleEventResource->save();
		
		// return the saved object
		$scheduleEventResource = new VidiunScheduleEventResource();
		$scheduleEventResource->fromObject($dbScheduleEventResource, $this->getResponseProfile());

		$resourceReservator->deleteReservation($scheduleEventResource->resourceId);
		return $scheduleEventResource;
	
	}
	
	/**
	 * Retrieve a VidiunScheduleEventResource object by ID
	 * 
	 * @action get
	 * @param int $scheduleEventId
	 * @param int $scheduleResourceId 
	 * @return VidiunScheduleEventResource
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function getAction($scheduleEventId, $scheduleResourceId)
	{
		$dbScheduleEventResource = ScheduleEventResourcePeer::retrieveByEventAndResource($scheduleEventId, $scheduleResourceId);
		if(!$dbScheduleEventResource)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, "$scheduleEventId,$scheduleResourceId");
		}
		
		$scheduleEventResource = new VidiunScheduleEventResource();
		$scheduleEventResource->fromObject($dbScheduleEventResource, $this->getResponseProfile());
		
		return $scheduleEventResource;
	}
	
	/**
	 * Update an existing VidiunScheduleEventResource object
	 * 
	 * @action update
	 * @param int $scheduleEventId
	 * @param int $scheduleResourceId 
	 * @param VidiunScheduleEventResource $scheduleEventResource
	 * @return VidiunScheduleEventResource
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function updateAction($scheduleEventId, $scheduleResourceId, VidiunScheduleEventResource $scheduleEventResource)
	{
		$dbScheduleEventResource = ScheduleEventResourcePeer::retrieveByEventAndResource($scheduleEventId, $scheduleResourceId);
		if(!$dbScheduleEventResource)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, "$scheduleEventId,$scheduleResourceId");
		}
		
		$dbScheduleEventResource = $scheduleEventResource->toUpdatableObject($dbScheduleEventResource);
		$dbScheduleEventResource->save();
		
		$scheduleEventResource = new VidiunScheduleEventResource();
		$scheduleEventResource->fromObject($dbScheduleEventResource, $this->getResponseProfile());
		
		return $scheduleEventResource;
	}
	
	/**
	 * Mark the VidiunScheduleEventResource object as deleted
	 * 
	 * @action delete
	 * @param int $scheduleEventId
	 * @param int $scheduleResourceId 
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function deleteAction($scheduleEventId, $scheduleResourceId)
	{
		$dbScheduleEventResource = ScheduleEventResourcePeer::retrieveByEventAndResource($scheduleEventId, $scheduleResourceId);
		if(!$dbScheduleEventResource)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, "$scheduleEventId,$scheduleResourceId");
		}
		
		$dbScheduleEventResource->delete();
		vEventsManager::raiseEvent(new vObjectErasedEvent($dbScheduleEventResource));
	}
	
	/**
	 * List VidiunScheduleEventResource objects
	 * 
	 * @action list
	 * @param VidiunScheduleEventResourceFilter $filter
	 * @param VidiunFilterPager $pager
	 * @param bool $filterBlackoutConflicts
	 * @return VidiunScheduleEventResourceListResponse
	 */
	public function listAction(VidiunScheduleEventResourceFilter $filter = null, VidiunFilterPager $pager = null,
							   $filterBlackoutConflicts = true)
	{
		if (!$filter)
		{
			$filter = new VidiunScheduleEventResourceFilter();
		}

		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}

		return $filter->getListResponse($pager, $this->getResponseProfile(), $filterBlackoutConflicts);
	}
}

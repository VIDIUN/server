<?php

/**
 * The ScheduleResource service enables you to create and manage (update, delete, retrieve, etc.) the resources required for scheduled events (cameras, capture devices, etc.).
 * @service scheduleResource
 * @package plugins.schedule
 * @subpackage api.services
 */
class ScheduleResourceService extends VidiunBaseService
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
	 * Allows you to add a new VidiunScheduleResource object
	 * 
	 * @action add
	 * @param VidiunScheduleResource $scheduleResource
	 * @return VidiunScheduleResource
	 */
	public function addAction(VidiunScheduleResource $scheduleResource)
	{
		// save in database
		$dbScheduleResource = $scheduleResource->toInsertableObject();
		$dbScheduleResource->save();
		
		// return the saved object
		$scheduleResource = VidiunScheduleResource::getInstance($dbScheduleResource, $this->getResponseProfile());
		$scheduleResource->fromObject($dbScheduleResource, $this->getResponseProfile());
		return $scheduleResource;
	
	}
	
	/**
	 * Retrieve a VidiunScheduleResource object by ID
	 * 
	 * @action get
	 * @param int $scheduleResourceId 
	 * @return VidiunScheduleResource
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function getAction($scheduleResourceId)
	{
		$dbScheduleResource = ScheduleResourcePeer::retrieveByPK($scheduleResourceId);
		if(!$dbScheduleResource)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $scheduleResourceId);
		}
		
		$scheduleResource = VidiunScheduleResource::getInstance($dbScheduleResource, $this->getResponseProfile());
		$scheduleResource->fromObject($dbScheduleResource, $this->getResponseProfile());
		
		return $scheduleResource;
	}
	
	/**
	 * Update an existing VidiunScheduleResource object
	 * 
	 * @action update
	 * @param int $scheduleResourceId
	 * @param VidiunScheduleResource $scheduleResource
	 * @return VidiunScheduleResource
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function updateAction($scheduleResourceId, VidiunScheduleResource $scheduleResource)
	{
		$dbScheduleResource = ScheduleResourcePeer::retrieveByPK($scheduleResourceId);
		if(!$dbScheduleResource)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $scheduleResourceId);
		}
		
		$dbScheduleResource = $scheduleResource->toUpdatableObject($dbScheduleResource);
		$dbScheduleResource->save();
		
		$scheduleResource = VidiunScheduleResource::getInstance($dbScheduleResource, $this->getResponseProfile());
		$scheduleResource->fromObject($dbScheduleResource, $this->getResponseProfile());
		
		return $scheduleResource;
	}
	
	/**
	 * Mark the VidiunScheduleResource object as deleted
	 * 
	 * @action delete
	 * @param int $scheduleResourceId 
	 * @return VidiunScheduleResource
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function deleteAction($scheduleResourceId)
	{
		$dbScheduleResource = ScheduleResourcePeer::retrieveByPK($scheduleResourceId);
		if(!$dbScheduleResource)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $scheduleResourceId);
		}
		
		$dbScheduleResource->setStatus(ScheduleResourceStatus::DELETED);
		$dbScheduleResource->save();
		
		$scheduleResource = VidiunScheduleResource::getInstance($dbScheduleResource, $this->getResponseProfile());
		$scheduleResource->fromObject($dbScheduleResource, $this->getResponseProfile());
		
		return $scheduleResource;
	}
	
	/**
	 * List VidiunScheduleResource objects
	 * 
	 * @action list
	 * @param VidiunScheduleResourceFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunScheduleResourceListResponse
	 */
	public function listAction(VidiunScheduleResourceFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunScheduleResourceFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
}

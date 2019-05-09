<?php
/**
 * @package plugins.schedule
 * @subpackage api.objects
 * @relatedService ScheduleEventResourceService
 * @abstract
 */
class VidiunScheduleEventResource extends VidiunObject implements IRelatedFilterable
{
	/**
	 * @var int
	 * @filter eq,in
	 * @insertonly
	 */
	public $eventId;

	/**
	 * @var int
	 * @filter eq,in
	 * @insertonly
	 */
	public $resourceId;

	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;

	/**
	 * Creation date as Unix timestamp (In seconds)
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;

	/**
	 * Last update as Unix timestamp (In seconds)
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;
	
	/*
	 * Mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array 
	 (	
		'eventId',
		'resourceId',
		'partnerId',
		'createdAt',
		'updatedAt',
	 );
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getExtraFilters()
	 */
	public function getExtraFilters()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getFilterDocs()
	 */
	public function getFilterDocs()
	{
		return array();
	}
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert($propertiesToSkip)
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull('eventId');
		$this->validatePropertyNotNull('resourceId');

		$c = new Criteria();
		$c->add(ScheduleEventResourcePeer::RESOURCE_ID, $this->resourceId);
		$c->add(ScheduleEventResourcePeer::EVENT_ID, $this->eventId);
		if(ScheduleEventResourcePeer::doCount($c))
			throw new VidiunAPIException(VidiunErrors::SCHEDULE_EVENT_RESOURCE_ALREADY_EXISTS, $this->eventId, $this->resourceId);

		if (is_null(ScheduleEventPeer::retrieveByPK($this->eventId)))
			throw new VidiunAPIException(VidiunErrors::SCHEDULE_EVENT_ID_NOT_FOUND, $this->eventId);

		if (is_null(ScheduleResourcePeer::retrieveByPK($this->resourceId)) && $this->resourceId != 0)
			throw new VidiunAPIException(VidiunErrors::SCHEDULE_RESOURCE_ID_NOT_FOUND, $this->resourceId);

		return parent::validateForInsert($propertiesToSkip);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($sourceObject = null, $propertiesToSkip = array())
	{
		if(!$sourceObject)
		{
			$sourceObject = new ScheduleEventResource();
		}
		
		return parent::toObject($sourceObject, $propertiesToSkip);
	}
}
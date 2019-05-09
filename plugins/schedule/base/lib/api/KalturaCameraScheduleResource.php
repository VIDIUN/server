<?php
/**
 * @package plugins.schedule
 * @subpackage api.objects
 */
class VidiunCameraScheduleResource extends VidiunScheduleResource
{
	/**
	 * URL of the stream
	 * @var string
	 * @minLength 1
	 * @maxLength 256
	 */
	public $streamUrl;
	
	/*
	 * Mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array 
	(	
		'streamUrl',
	);
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * {@inheritDoc}
	 * @see VidiunScheduleResource::getScheduleResourceType()
	 */
	protected function getScheduleResourceType()
	{
		return ScheduleResourceType::CAMERA;
	}
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert($propertiesToSkip)
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		return parent::validateForInsert($propertiesToSkip);
	}
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUpdate($sourceObject, $propertiesToSkip)
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		if($this->streamUrl instanceof VidiunNullField)
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, $this->getFormattedPropertyNameWithClassName('streamUrl'));
		}
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($sourceObject = null, $propertiesToSkip = array())
	{
		if(is_null($sourceObject))
		{
			$sourceObject = new CameraScheduleResource();
		}
		
		return parent::toObject($sourceObject, $propertiesToSkip);
	}
}
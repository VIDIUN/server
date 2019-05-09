<?php
/**
 * Used to ingest media that is already ingested to Vidiun system as a different file in the past, the new created flavor asset will be ready immediately using a file sync of link type that will point to the existing file sync.
 * 
 * @package api
 * @subpackage objects
 */
class VidiunFileSyncResource extends VidiunContentResource
{
	/**
	 * The object type of the file sync object 
	 * @var int
	 */
	public $fileSyncObjectType;
	
	/**
	 * The object sub-type of the file sync object 
	 * @var int
	 */
	public $objectSubType;
	
	/**
	 * The object id of the file sync object 
	 * @var string
	 */
	public $objectId;
	
	/**
	 * The version of the file sync object 
	 * @var string
	 */
	public $version;
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$this->validatePropertyNotNull('fileSyncObjectType');
		$this->validatePropertyNotNull('objectSubType');
		$this->validatePropertyNotNull('objectId');
	}
	
	private static $map_between_objects = array('fileSyncObjectType', 'objectSubType', 'objectId', 'version');
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if(!$object_to_fill)
			$object_to_fill = new vFileSyncResource();
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}
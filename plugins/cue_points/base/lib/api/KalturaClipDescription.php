<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunClipDescription extends VidiunObject
{
	/**
	 * @var string
	 */
	public $sourceEntryId;
	
	/**
	 * 
	 * @var int
	 */
	public $startTime;
	
	/**
	 * 
	 * @var int
	 */
	public $duration;

	/**
	 *
	 * @var int
	 */
	public $offsetInDestination;
	
	private static $map_between_objects = array
	(
		"sourceEntryId" ,
		"startTime" ,
		"duration" ,
		"offsetInDestination"
	);


	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vClipDescription();
			
		return parent::toObject($dbObject, $skip);
	}
}
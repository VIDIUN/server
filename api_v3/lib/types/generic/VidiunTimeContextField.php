<?php
/**
 * Represents the current time context on Vidiun servers
 * 
 * @package api
 * @subpackage objects
 */
class VidiunTimeContextField extends VidiunIntegerField
{
	/**
	 * Time offset in seconds since current time
	 * @var int
	 */
	public $offset;

	static private $map_between_objects = array
	(
		'offset',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vTimeContextField();
			
		return parent::toObject($dbObject, $skip);
	}
}
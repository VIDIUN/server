<?php
/**
 * Represents the current request country context as calculated based on the IP address
 * 
 * @package api
 * @subpackage objects
 */
class VidiunCoordinatesContextField extends VidiunStringField
{
	/**
	 * The ip geo coder engine to be used
	 * 
	 * @var VidiunGeoCoderType
	 */
	public $geoCoderType = geoCoderType::VIDIUN;
	
	static private $map_between_objects = array
	(
		'geoCoderType',
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
			$dbObject = new vCoordinatesContextField();
			
		return parent::toObject($dbObject, $skip);
	}
}
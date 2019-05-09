<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunCountryCondition extends VidiunMatchCondition
{
	/**
	 * The ip geo coder engine to be used
	 * 
	 * @var VidiunGeoCoderType
	 */
	public $geoCoderType;

	private static $mapBetweenObjects = array
	(
		'geoCoderType',
	);

	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::COUNTRY;
	}
		
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vCountryCondition();
			
		return parent::toObject($dbObject, $skip);
	}
}

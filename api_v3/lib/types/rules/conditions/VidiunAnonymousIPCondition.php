<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAnonymousIPCondition extends VidiunMatchCondition
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
		$this->type = ConditionType::ANONYMOUS_IP;
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
			$dbObject = new vAnonymousIPCondition();
			
		return parent::toObject($dbObject, $skip);
	}
}

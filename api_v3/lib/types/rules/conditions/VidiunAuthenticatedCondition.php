<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAuthenticatedCondition extends VidiunCondition
{
	/**
	 * The privelege needed to remove the restriction
	 * 
	 * @var VidiunStringValueArray
	 */
	public $privileges;
	
	private static $mapBetweenObjects = array
	(
		'privileges',
	);
	
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::AUTHENTICATED;
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
			$dbObject = new vAuthenticatedCondition();
			
		return parent::toObject($dbObject, $skip);
	}
}

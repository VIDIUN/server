<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunOrCondition extends VidiunCondition
{
	/**
	 * @var VidiunConditionArray
	 */
	public $conditions;
	
	private static $mapBetweenObjects = array
	(
		'conditions',
	);
	
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::OR_OPERATOR;
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
			$dbObject = new vOrCondition();
			
		return parent::toObject($dbObject, $skip);
	}
}

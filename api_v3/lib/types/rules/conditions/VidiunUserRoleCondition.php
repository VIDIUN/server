<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUserRoleCondition extends VidiunCondition
{
	/**
	 * Comma separated list of role ids
	 * 
	 * @var string
	 */
	public $roleIds;
	
	private static $mapBetweenObjects = array
	(
		'roleIds',
	);
	
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::USER_ROLE;
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
			$dbObject = new vUserRoleCondition();
			
		return parent::toObject($dbObject, $skip);
	}
}

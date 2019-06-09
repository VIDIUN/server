<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAccessControlLimitDeliveryProfilesAction extends VidiunRuleAction
{
	/**
	 * Comma separated list of delivery profile ids 
	 * 
	 * @var string
	 */
	public $deliveryProfileIds;
	
	/**
	 * @var bool
	 */
	public $isBlockedList;
	
	private static $mapBetweenObjects = array
	(
		'deliveryProfileIds',
		'isBlockedList',
	);

	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = RuleActionType::LIMIT_DELIVERY_PROFILES;
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
			$dbObject = new vAccessControlLimitDeliveryProfilesAction();
			
		return parent::toObject($dbObject, $skip);
	}
}
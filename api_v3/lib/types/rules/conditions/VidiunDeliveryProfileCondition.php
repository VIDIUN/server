<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDeliveryProfileCondition extends VidiunCondition
{
	/**
	 * The delivery ids that are accepted by this condition
	 * 
	 * @var VidiunIntegerValueArray
	 */
	public $deliveryProfileIds;
	
	private static $mapBetweenObjects = array
	(
		'deliveryProfileIds',
	);
	
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
			$dbObject = new vDeliveryProfileCondition();
		return parent::toObject($dbObject, $skip);
	}
}

<?php
/**
 * @package api
 * @subpackage objects
 * @abstract
 */
abstract class VidiunMatchCondition extends VidiunCondition
{
	/**
	 * @var VidiunStringValueArray
	 */
	public $values;
	
	/**
	 * @var VidiunMatchConditionType
	 */
	public $matchType;
	
	private static $mapBetweenObjects = array
	(
		'values',
		'matchType',
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
}

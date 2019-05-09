<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunContextDataResult extends VidiunObject
{	
	/**
	 * Array of messages as received from the rules that invalidated
	 * @var VidiunStringArray
	 */
	public $messages;
	
	/**
	 * Array of actions as received from the rules that invalidated
	 * @var VidiunRuleActionArray
	 */
	public $actions;

	private static $mapBetweenObjects = array
	(
		'messages',
		'actions',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
}
<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunPlayerDeliveryType extends VidiunObject
{
	/**
	 * @var string
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $label;
	
	/**
	 * @var VidiunKeyValueArray
	 */
	public $flashvars;
	
	/**
	 * @var string
	 */
	public $minVersion;

	/**
	 * @var bool
	 */
	public $enabledByDefault = false;
	
	private static $map_between_objects = array(
		'label', 
		'flashvars',
		'minVersion',
		'enabledByDefault'
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}
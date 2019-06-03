<?php
/**
 * @package api
 * @subpackage objects
 */
abstract class VidiunDeliveryServerNode extends VidiunServerNode
{
	/**
	 * Delivery profile ids
	 * @var VidiunKeyValueArray
	 */
	public $deliveryProfileIds;

	/**
	 * Override server node default configuration - json format
	 * @var string
	 */
	public $config;

	private static $map_between_objects = array 
	(
		"deliveryProfileIds",
		"config",
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}
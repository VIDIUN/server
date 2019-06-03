<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDeliveryProfileAkamaiHttp extends VidiunDeliveryProfile {
	
	/**
	 * Should we use intelliseek
	 * 
	 * @var bool
	 */
	public $useIntelliseek;
	
	private static $map_between_objects = array
	(
			"useIntelliseek",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
}


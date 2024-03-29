<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDeliveryProfileLivePackager extends VidiunDeliveryProfile {
	
	/**
	 * Domain used to sign the live url
	 * @var string
	 */
	public $livePackagerSigningDomain;
	
	private static $map_between_objects = array
	(
		"livePackagerSigningDomain",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
}


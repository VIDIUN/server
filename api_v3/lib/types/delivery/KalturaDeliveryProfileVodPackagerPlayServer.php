<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDeliveryProfileVodPackagerPlayServer extends VidiunDeliveryProfile
{
	/**
	 * @var bool
	 */
	public $adStitchingEnabled;

	private static $map_between_objects = array
	(
		'adStitchingEnabled',
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}
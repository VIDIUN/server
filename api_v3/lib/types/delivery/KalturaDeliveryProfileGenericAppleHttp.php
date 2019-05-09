<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDeliveryProfileGenericAppleHttp extends VidiunDeliveryProfile {
	
	/**
	 * @var string
	 */
	public $pattern;
	
	/**
	 * rendererClass
	 * @var string
	 */
	public $rendererClass;
	
	/**
	 * Enable to make playManifest redirect to the domain of the delivery profile
	 *
	 * @var VidiunNullableBoolean
	 */
	public $manifestRedirect;
	
	
	private static $map_between_objects = array
	(
			"pattern",
			"rendererClass",
			"manifestRedirect",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}


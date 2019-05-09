<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFlavorAssetUrlOptions extends VidiunObject
{
	/**
	 * The name of the downloaded file
	 * 
	 * @var string
	 */
	public $fileName;

	/**
	 *
	 * @var string
	 */
	public $referrer;
		
	private static $map_between_objects = array
	(
		"fileName",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}

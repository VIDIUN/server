<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunRemotePath extends VidiunObject
{
	/**
	 * @var int
	 * @readonly
	 */
	public $storageProfileId;
	
	/**
	 * @var string
	 * @readonly
	 */
	public $uri;
	
	private static $map_between_objects = array
	(
		"storageProfileId" => "dc",
		"uri" => "filePath",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}	
}
<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlTokenizerCloudFront extends VidiunUrlTokenizer {

	/**
	 * @var string
	 */
	public $keyPairId;
	
	/**
	 * @var string
	 */
	public $rootDir;
	
	private static $map_between_objects = array
	(
			"keyPairId",
			"rootDir",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vCloudFrontUrlTokenizer();
			
		parent::toObject($dbObject, $skip);
	
		return $dbObject;
	}
}

<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlTokenizerBitGravity extends VidiunUrlTokenizer {

	/**
	 * hashPatternRegex
	 *
	 * @var string
	 */
	public $hashPatternRegex;
	
	private static $map_between_objects = array
	(
			"hashPatternRegex",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vBitGravityUrlTokenizer();
			
		parent::toObject($dbObject, $skip);
	
		return $dbObject;
	}
}

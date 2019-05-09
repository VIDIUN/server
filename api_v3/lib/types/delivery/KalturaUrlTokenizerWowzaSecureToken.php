<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlTokenizerWowzaSecureToken extends VidiunUrlTokenizer
{	
	/**
	 * @var string
	 */
	public $paramPrefix;
	
	/**
	 * @var string
	 */
	public $hashAlgorithm;
	
	private static $map_between_objects = array
	(
		"paramPrefix",
		"hashAlgorithm"
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vWowzaSecureTokenUrlTokenizer();
			
		parent::toObject($dbObject, $skip);
	
		return $dbObject;
	}
}

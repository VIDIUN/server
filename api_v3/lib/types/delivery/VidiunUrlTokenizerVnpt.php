<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlTokenizerVnpt extends VidiunUrlTokenizer {

	/**
	 * @var int
	 */
	public $tokenizationFormat;

	/**
	 * @var bool
	 */
	public $shouldIncludeClientIp;

	private static $map_between_objects = array
	(
			"tokenizationFormat",
			"shouldIncludeClientIp",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vVnptUrlTokenizer();

		parent::toObject($dbObject, $skip);
	
		return $dbObject;
	}
}

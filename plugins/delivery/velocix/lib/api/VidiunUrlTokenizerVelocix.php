<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlTokenizerVelocix extends VidiunUrlTokenizer {
	
	/**
	 * hdsPaths
	 *
	 * @var string
	 */
	public $hdsPaths;
	
	/**
	 * tokenParamName
	 *
	 * @var string
	 */
	public $paramName;
	
	/**
	 * secure URL prefix
	 * @var string
	 */
	public $authPrefix;
	
	private static $map_between_objects = array
	(
			"hdsPaths",
			"paramName",
			"authPrefix"
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vVelocixUrlTokenizer();
			
		parent::toObject($dbObject, $skip);
	
		return $dbObject;
	}
}

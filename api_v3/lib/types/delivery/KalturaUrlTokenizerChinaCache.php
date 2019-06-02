<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlTokenizerChinaCache extends VidiunUrlTokenizer {

	/**
	 * @var VidiunChinaCacheAlgorithmType
	 */
	public $algorithmId;
	
	/**
	 * @var int
	 */
	public $keyId;
	
	private static $map_between_objects = array
	(
			"algorithmId",
			"keyId",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vChinaCacheUrlTokenizer();
			
		parent::toObject($dbObject, $skip);
	
		return $dbObject;
	}
}

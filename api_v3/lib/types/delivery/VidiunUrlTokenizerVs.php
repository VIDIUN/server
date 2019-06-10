<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlTokenizerVs extends VidiunUrlTokenizer
{
	/**
	 * @var bool
	 */
	public $usePath;

	/**
	 * @var string
	 */
	public $additionalUris;

	private static $map_between_objects = array
	(
			"usePath",
			"additionalUris",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vVsUrlTokenizer();

		parent::toObject($dbObject, $skip);

		return $dbObject;
	}
}

<?php
/**
 * Configuration for extended item in the Vidiun MRSS feeds
 *
 * @package api
 * @subpackage objects
 */
abstract class VidiunObjectIdentifier extends VidiunObject
{
	/**
	 * Comma separated string of enum values denoting which features of the item need to be included in the MRSS 
	 * @dynamicType VidiunObjectFeatureType
	 * @var string
	 */
	public $extendedFeatures;
	
	
	private static $map_between_objects = array(
		"extendedFeatures",
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

}
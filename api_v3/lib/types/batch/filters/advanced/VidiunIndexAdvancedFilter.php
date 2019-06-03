<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunIndexAdvancedFilter extends VidiunSearchItem
{
	/**
	 * @var int
	 */
	public $indexIdGreaterThan;

	/**
	 * @var int
	 */
	public $depthGreaterThanEqual;

	private static $map_between_objects = array
	(
		"indexIdGreaterThan",
		"depthGreaterThanEqual",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new kIndexAdvancedFilter();
			
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}

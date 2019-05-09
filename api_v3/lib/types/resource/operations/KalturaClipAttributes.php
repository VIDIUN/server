<?php
/**
 * Clip operation attributes
 * 
 * @package api
 * @subpackage objects
 */
class VidiunClipAttributes extends VidiunOperationAttributes
{
	/**
	 * Offset in milliseconds
	 * @var int
	 * @requiresPermission all
	 */
	public $offset;
	
	/**
	 * Duration in milliseconds
	 * @var int
	 * @requiresPermission all
	 */
	public $duration;

	/**
	 * global Offset In Destination in milliseconds
	 * @var int
	 */
	public $globalOffsetInDestination;

	/**
	 * global Offset In Destination in milliseconds
	 * @var VidiunEffectsArray
	 */
	public $effectArray;

	private static $map_between_objects = array
	(
	 	"offset" , 
	 	"duration",
		"globalOffsetInDestination",
		"effectArray"
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}	
	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(is_null($object_to_fill))
			$object_to_fill = new vClipAttributes();
			
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}
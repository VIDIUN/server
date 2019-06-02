<?php
/**
 * A string representation to return an array of strings
 * 
 * @see VidiunStringArray
 * @package api
 * @subpackage objects
 */
class VidiunString extends VidiunObject
{
	/**
	 * @var string
	 */
    public $value;
    
	private static $mapBetweenObjects = array
	(
		"value",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
	    return $this->value;
	}
}
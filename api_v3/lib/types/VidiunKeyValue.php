<?php
/**
 * A key value pair representation to return an array of key-value pairs (associative array)
 * 
 * @see VidiunKeyValueArray
 * @package api
 * @subpackage objects
 */
class VidiunKeyValue extends VidiunObject
{
	/**
	 * @var string
	 */
	public $key;
    
	/**
	 * @var string
	 */
	public $value;
    
	private static $mapBetweenObjects = array
	(
		"key", "value",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
}
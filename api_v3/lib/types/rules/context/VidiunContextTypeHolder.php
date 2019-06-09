<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunContextTypeHolder extends VidiunObject
{
	/**
	 * The type of the condition context
	 * 
	 * @var VidiunContextType
	 */
	public $type;
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		return $this->type;
	}
	
	private static $mapBetweenObjects = array
	(
		'type',
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
}
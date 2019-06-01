<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunMediaServerStatus extends VidiunObject
{
	private static $mapBetweenObjects = array
	(
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
}
<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFlattenJobData extends VidiunJobData
{
	private static $map_between_objects = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbData = null, $propsToSkip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vFlattenJobData();
			
		return parent::toObject($dbData);
	}
}

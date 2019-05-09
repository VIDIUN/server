<?php

/**
 * @package plugins.dropFolder
 * @subpackage api.objects
 */
class VidiunDropFolderImportJobData extends VidiunSshImportJobData
{
	/**
	 * @var int
	 */
	public $dropFolderFileId;
	
	
	private static $map_between_objects = array
	(
		"dropFolderFileId",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vDropFolderImportJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
}
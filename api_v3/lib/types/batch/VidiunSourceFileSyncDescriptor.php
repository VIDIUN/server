<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunSourceFileSyncDescriptor extends VidiunFileSyncDescriptor
{
	/**
	 * The translated path as used by the scheduler
	 * @var string
	 */
	public $actualFileSyncLocalPath;
	
	/**
	 * 
	 * @var string
	 */
	public $assetId;
	
	/**
	 * 
	 * @var int
	 */
	public $assetParamsId;
	
	private static $map_between_objects = array
	(
		"actualFileSyncLocalPath" ,
		"assetId" ,
		"assetParamsId" ,
	);


	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vSourceFileSyncDescriptor();
			
		return parent::toObject($dbObject, $skip);
	}
}
<?php
/**
 * @package plugins.widevine
 * @subpackage api.objects
 */
class VidiunWidevineRepositorySyncJobData extends VidiunJobData
{
	/**
	 * 
	 * @var VidiunWidevineRepositorySyncMode
	 */
	public $syncMode;
	
	/**
	 * @var string
	 */
	public $wvAssetIds;
	
	/**
	 * @var string
	 */
	public $modifiedAttributes;
	
	/**
	 * @var int
	 */
	public $monitorSyncCompletion;
		
	private static $map_between_objects = array
	(
		"syncMode",
		"wvAssetIds",
		"modifiedAttributes",
		"monitorSyncCompletion"
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}
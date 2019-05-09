<?php
/**
 * @package plugins.multiCenters
 * @subpackage api.objects
 */
class VidiunLockFileSyncsResponse extends VidiunObject
{
	/**
	 * @var VidiunFileSyncArray
	 */
	public $fileSyncs;
		
	/**
	 * @var bool
	 */
	public $limitReached;
	
	/**
	 * @var string
	 */
	public $dcSecret;
	
	/**
	 * @var string
	 */
	public $baseUrl;
	
	private static $map_between_objects = array
	(
		"fileSyncs",
		"limitReached",
		"dcSecret",
		"baseUrl",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}

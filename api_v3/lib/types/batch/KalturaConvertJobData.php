<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunConvertJobData extends VidiunConvartableJobData
{
	/**
	 * @var string
	 */
	public $destFileSyncLocalPath;
	
	/**
	 * @var string
	 */
	public $destFileSyncRemoteUrl;
	
	/**
	 * @var string
	 */
	public $logFileSyncLocalPath;
	
	/**
	 * @var string
	 */
	 public $logFileSyncRemoteUrl;
	
	/**
	 * @var string
	 */
	public $flavorAssetId;
	
	
	/**
	 * @var string
	 */
	public $remoteMediaId;
    
	/**
	 * @var string
	 */
	public $customData;
	
	/**
	 * 
	 * @var VidiunDestFileSyncDescriptorArray
	 */
	public $extraDestFileSyncs;
	
	/**
	 * @var string
	 */
	public $engineMessage;
    
	private static $map_between_objects = array
	(
		"destFileSyncLocalPath" ,
		"destFileSyncRemoteUrl" ,
		"logFileSyncLocalPath" ,
		"logFileSyncRemoteUrl" ,
		"flavorAssetId" ,
		"remoteMediaId" ,
		"customData" ,
		"extraDestFileSyncs",
		"engineMessage",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vConvertJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
	
	/**
	 * @param string $subType
	 * @return int
	 */
	public function toSubType($subType)
	{
		return vPluginableEnumsManager::apiToCore('conversionEngineType', $subType);
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		return vPluginableEnumsManager::coreToApi('conversionEngineType', $subType);
	}
}

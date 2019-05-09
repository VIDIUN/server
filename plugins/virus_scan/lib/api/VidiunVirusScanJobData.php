<?php
/**
 * @package plugins.virusScan
 * @subpackage api.objects
 */
class VidiunVirusScanJobData extends VidiunJobData
{
	
	/**
	 * @var VidiunFileContainer
	 */
	public $fileContainer;
	
	/**
	 * @var string
	 */
	public $flavorAssetId;
	
	/**
	 * @var VidiunVirusScanJobResult
	 */
	public $scanResult;
	
	/**
	 * @var VidiunVirusFoundAction
	 */
	public $virusFoundAction;
	
	
	private static $map_between_objects = array
	(
		"fileContainer",
		"flavorAssetId" ,
		"scanResult" ,
		"virusFoundAction",
	);


	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/**
	 * @param string $subType
	 * @return int
	 */
	public function toSubType($subType)
	{
		return vPluginableEnumsManager::apiToCore('VirusScanEngineType', $subType);
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		return vPluginableEnumsManager::coreToApi('VirusScanEngineType', $subType);
	}

}

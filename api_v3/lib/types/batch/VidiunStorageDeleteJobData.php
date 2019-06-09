<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunStorageDeleteJobData extends VidiunStorageJobData
{
	private static $map_between_objects = array
	(
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vStorageDeleteJobData();
			
		return parent::toObject($dbData);
	}
	
	/**
	 * @param string $subType
	 * @return int
	 */
	public function toSubType($subType)
	{
		switch ($subType) {
			case VidiunStorageProfileProtocol::SFTP:
            case VidiunStorageProfileProtocol::FTP:
            case VidiunStorageProfileProtocol::SCP:
            case VidiunStorageProfileProtocol::S3:
            case VidiunStorageProfileProtocol::VIDIUN_DC:
                return $subType;	
			default:
				return vPluginableEnumsManager::apiToCore('VidiunStorageProfileProtocol', $subType);
		}
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		switch ($subType) {
            case StorageProfileProtocol::SFTP:
            case StorageProfileProtocol::FTP:
            case StorageProfileProtocol::SCP:
            case StorageProfileProtocol::S3:
            case StorageProfileProtocol::VIDIUN_DC:
                return $subType;    
            default:
                return vPluginableEnumsManager::coreToApi('StorageProfileProtocol', $subType);
        }
	}
}


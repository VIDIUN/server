<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.objects
 */
class VidiunDropFolderContentProcessorJobData extends VidiunJobData
{
	
	/**
	 * @var int
	 */
	public $dropFolderId;
	
	/**
	 * @var string
	 */
	public $dropFolderFileIds;
	
	/**
	 * @var string
	 */
	public $parsedSlug;
	
	/**
	 * @var VidiunDropFolderContentFileHandlerMatchPolicy
	 */
	public $contentMatchPolicy;
	
	/**
	 * @var int
	 */
	public $conversionProfileId;
	
	/**
	 * @var string
	 */
	public $parsedUserId;
	
	private static $map_between_objects = array
	(
		"dropFolderId",
		"dropFolderFileIds",
		"parsedSlug",
		"contentMatchPolicy",
		"conversionProfileId",
		"parsedUserId",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vDropFolderContentProcessorJobData();
		
		return parent::toObject($dbData, $props_to_skip);
	}

	
	/**
	 * @param string $subType
	 * @return int
	 */
	public function toSubType($subType)
	{
		switch ($subType) {
			case VidiunDropFolderType::FTP:
            case VidiunDropFolderType::SFTP:
            case VidiunDropFolderType::SCP:
            case VidiunDropFolderType::S3:
                return $subType;                  	
			default:
				return vPluginableEnumsManager::apiToCore('VidiunDropFolderType', $subType);
		}
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		switch ($subType) {
            case DropFolderType::FTP:
            case DropFolderType::SFTP:
            case DropFolderType::SCP:
            case DropFolderType::S3:
                return $subType;                    
            default:
                return vPluginableEnumsManager::coreToApi('DropFolderType', $subType);
        }
	}
}
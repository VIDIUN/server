<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAmazonS3StorageExportJobData extends VidiunStorageExportJobData 
{
	/**
	 * @var VidiunAmazonS3StorageProfileFilesPermissionLevel
	 */   	
    public $filesPermissionInS3;   
    
	/**
	 * @var string
	 */   	
    public $s3Region;   
	
	/**
	 * @var string
	 */   	
    public $sseType;   
	
	/**
	 * @var string
	 */   	
    public $sseVmsKeyId;   
    
	/**
	 * @var string
	 */   	
    public $signatureType;   
    
    	/**
	 * @var string
	 */   	
    public $endPoint;   
    
    private static $map_between_objects = array
	(
		"filesPermissionInS3",	
		"s3Region",	
		"sseType",
		"sseVmsKeyId",
		"signatureType",
		"endPoint",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vAmazonS3StorageExportJobData();
			
		return parent::toObject($dbData);
	}
	
}
<?php
/**
 * @package plugins.kontiki
 * @subpackage api.object
 */
 class VidiunKontikiStorageExportJobData extends VidiunStorageExportJobData
 {
	/**
	 * Holds the id of the exported asset
	 * @var string
	 */
 	public $flavorAssetId;
	
	/**
	 * Unique Kontiki MOID for the content uploaded to Kontiki
	 * @var string
	 */
	public $contentMoid;
	
	/**
	 * @var string
	 */
	public $serviceToken;
	
    
    private static $map_between_objects = array
    (
        'flavorAssetId',
        'serviceToken',
        'contentMoid',
    );
	
    /* (non-PHPdoc)
     * @see VidiunObject::getMapBetweenObjects()
     */
    public function getMapBetweenObjects ( )
    {
        return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
    }
    
 	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vKontikiStorageExportJobData();
			
		return parent::toObject($dbData);
	}
 }

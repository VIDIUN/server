<?php
/**
 * @package plugins.kontiki
 * @subpackage api.object
 */
class VidiunKontikiStorageDeleteJobData extends VidiunStorageDeleteJobData
{
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
    
    /* (non-PHPdoc)
     * @see VidiunObject::toObject()
     */
    public function toObject($dbData = null, $props_to_skip = array()) 
    {
        if(is_null($dbData))
            $dbData = new vKontikiStorageDeleteJobData();
            
        return parent::toObject($dbData);
    }
    
}

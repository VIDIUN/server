<?php
/**
 * @package plugins.scheduleBulkUpload
 * @subpackage api.objects
 */
class VidiunBulkUploadResultScheduleResource extends VidiunBulkUploadResult
{
    /**
     * @var string
     */
    public $resourceId;
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var string
     */
    public $type;
    
    /**
     * @var string
     */
    public $systemName;
    
    /**
     * @var string
     */
    public $description;
    
    /**
     * @var string
     */
    public $tags;
    
    /**
     * @var string
     */
    public $parentType;
    
    /**
     * @var string
     */
    public $parentSystemName;

    private static $mapBetweenObjects = array
	(
	    'resourceId',
	    'name' => 'title',
	    'type',
	    'systemName',
	    'description',
	    'tags',
	    'parentType',
	    'parentSystemName',
	);
	
    public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
    /* (non-PHPdoc)
     * @see VidiunBulkUploadResult::toInsertableObject()
     */
    public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		return parent::toInsertableObject(new BulkUploadResultScheduleResource(), $props_to_skip);
	}
}
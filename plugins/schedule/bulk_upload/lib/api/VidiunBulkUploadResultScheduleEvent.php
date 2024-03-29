<?php
/**
 * @package plugins.scheduleBulkUpload
 * @subpackage api.objects
 */
class VidiunBulkUploadResultScheduleEvent extends VidiunBulkUploadResult
{
    
    /**
     * @var string
     */
    public $referenceId;
    
    private static $mapBetweenObjects = array
	(
	    "referenceId",
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
		return parent::toInsertableObject(new BulkUploadResultScheduleEvent(), $props_to_skip);
	}
}
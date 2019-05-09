<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBulkUploadResultCategoryEntry extends VidiunBulkUploadResult
{
    /**
     * @var int
     */
    public $categoryId;
    /**
     * @var string
     */
    public $entryId;
    
    private static $mapBetweenObjects = array
	(
	    "categoryId",
		"entryId",
	);
	
    public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
    public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		return parent::toInsertableObject(new BulkUploadResultCategoryEntry(), $props_to_skip);
	}
}
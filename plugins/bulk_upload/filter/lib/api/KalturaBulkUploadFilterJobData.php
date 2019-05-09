<?php

/**
 * Represents the Bulk upload job data for filter bulk upload
 * @package plugins.bulkUploadFilter
 * @subpackage api.objects
 */
class VidiunBulkUploadFilterJobData extends VidiunBulkUploadJobData
{	
	/**
	 * Filter for extracting the objects list to upload 
	 * @var VidiunFilter
	 */
	public $filter;

	/**
	 * Template object for new object creation
	 * @var VidiunObject
	 */
	public $templateObject;
	
	/**
	 * 
	 * Maps between objects and the properties
	 * @var array
	 */
	private static $map_between_objects = array
	(
		"filter",
	);

	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vBulkUploadFilterJobData();
		
		switch (get_class($this->templateObject))
	    {
	        case 'VidiunCategoryEntry':
	           	$dbData->setTemplateObject(new categoryEntry());
	           	$this->templateObject->toObject($dbData->getTemplateObject());
	            break;
	        default:
	            break;
	    }
	    
		return parent::toObject($dbData);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
	    parent::doFromObject($source_object, $responseProfile);
	    
	    /* @var $source_object vBulkUploadFilterJobData */
	    $this->filter = null;
	    switch (get_class($source_object->getFilter()))
	    {
	        case 'categoryEntryFilter':
	            $this->filter = new VidiunCategoryEntryFilter();
	            break;
	        case 'entryFilter':
	            $this->filter = new VidiunBaseEntryFilter();
	            break;
		case 'UserEntryFilter':
	  	    $this->filter = new VidiunUserEntryFilter();
		    break;
	        default:
	            break;
	    }
	    
	    if ($this->filter)
	    {
	        $this->filter->fromObject($source_object->getFilter());
	    }       
	    
	   	$this->templateObject = null;
	   	
	    switch (get_class($source_object->getTemplateObject()))
	    {
	        case 'categoryEntry':
	            $this->templateObject = new VidiunCategoryEntry();
	            break;
	        default:
	            break;
	    }
	    
	    if ($this->templateObject)
	    {
	        $this->templateObject->fromObject($source_object->getTemplateObject());
	    }       
	}
	
	public function toInsertableObject($object_to_fill = null , $props_to_skip = array())
	{
	    $dbObj = parent::toInsertableObject($object_to_fill, $props_to_skip);
	    
	    $this->setType();
	    
	    return $dbObj;
	}
	
	public function setType ()
	{
	    $this->type = vPluginableEnumsManager::coreToApi("VidiunBulkUploadType", BulkUploadFilterPlugin::getApiValue(BulkUploadFilterType::FILTER));
	}
}

<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunCopyJobData extends VidiunJobData
{
	/**
	 * The filter should return the list of objects that need to be copied.
	 * @var VidiunFilter
	 */
	public $filter;
	
	/**
	 * Indicates the last id that copied, used when the batch crached, to re-run from the last crash point.
	 * @var int
	 */
	public $lastCopyId;
	
	/**
	 * Template object to overwrite attributes on the copied object
	 * @var VidiunObject
	 */
	public $templateObject;
	
	private static $map_between_objects = array
	(
		"lastCopyId" ,
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
			$dbData = new vCopyJobData();
			
		$dbData->setTemplateObject($this->templateObject->toObject());
		
		return parent::toObject($dbData, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbData, VidiunDetachedResponseProfile $responseProfile = null) 
	{
		/* @var $dbData vCopyJobData */
		$filter = $dbData->getFilter();
		$filterType = get_class($filter);
		switch($filterType)
		{
			case 'entryFilter':
				$this->filter = new VidiunBaseEntryFilter();
				$this->templateObject = new VidiunBaseEntry();
				break;
				
			case 'categoryFilter':
				$this->filter = new VidiunCategoryFilter();
				$this->templateObject = new VidiunCategory();
				break;
				
			case 'categoryEntryFilter':
				$this->filter = new VidiunCategoryEntryFilter();
				$this->templateObject = new VidiunCategoryEntry();
				break;
				
			case 'categoryVuserFilter':
				$this->filter = new VidiunCategoryUserFilter();
				$this->templateObject = new VidiunCategoryUser();
				break;
				
			default:
				$this->filter = VidiunPluginManager::loadObject('VidiunFilter', $filterType);
		}
		if($this->filter)
			$this->filter->fromObject($filter);
		
		if($this->templateObject)
			$this->templateObject->fromObject($dbData->getTemplateObject());
		
		parent::doFromObject($dbData, $responseProfile);
	}
}

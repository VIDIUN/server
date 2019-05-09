<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDeleteJobData extends VidiunJobData
{
	/**
	 * The filter should return the list of objects that need to be deleted.
	 * @var VidiunFilter
	 */
	public $filter;
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vDeleteJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
	
	public function doFromObject($dbData, VidiunDetachedResponseProfile $responseProfile = null) 
	{
		/* @var $dbData vDeleteJobData */
		$filter = $dbData->getFilter();
		$filterType = get_class($filter);
		switch($filterType)
		{
			case 'categoryEntryFilter':
				$this->filter = new VidiunCategoryEntryFilter();
				break;
				
			case 'categoryVuserFilter':
				$this->filter = new VidiunCategoryUserFilter();
				break;

			case 'VuserVgroupFilter':
				$this->filter = new VidiunGroupUserFilter();
				break;
				
			case 'categoryFilter':
				$this->filter = new VidiunCategoryFilter();
 				break;
				
			case 'UserEntryFilter':
				$this->filter = new VidiunUserEntryFilter();
 				break;
			
			default:
				$this->filter = VidiunPluginManager::loadObject('VidiunFilter', $filterType);
		}
		if($this->filter)
			$this->filter->fromObject($filter);
		
		parent::doFromObject($dbData, $responseProfile);
	}
}

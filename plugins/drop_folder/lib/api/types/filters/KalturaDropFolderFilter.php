<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.filters
 */
class VidiunDropFolderFilter extends VidiunDropFolderBaseFilter
{
	/**
	 * @var VidiunNullableBoolean
	 */
	public $currentDc;

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new DropFolderFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::toObject()
	 */
	public function toObject ( $object_to_fill = null, $props_to_skip = array() )
	{
		if(!$this->isNull('currentDc'))
			$this->dcEqual = vDataCenterMgr::getCurrentDcId();
			
		return parent::toObject($object_to_fill, $props_to_skip);		
	}	
}

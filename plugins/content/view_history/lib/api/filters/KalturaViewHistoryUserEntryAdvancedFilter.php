<?php
/**
 * @package plugins.viewHistory
 * @subpackage api.filters
 */
class VidiunViewHistoryUserEntryAdvancedFilter extends VidiunSearchItem
{
	/**
	 * @var string
	 */
	public $idEqual;
	
	/**
	 * @var string
	 */
	public $idIn;
	
	/**
	 * @var string
	 */
	public $userIdEqual;
	
	/**
	 * @var string
	 */
	public $userIdIn;
	
	/**
	 * @var string
	 */
	public $updatedAtGreaterThanOrEqual;
	
	/**
	 * @var string
	 */
	public $updatedAtLessThanOrEqual;
	
	/**
	 * @var VidiunUserEntryExtendedStatus
	 */
	public $extendedStatusEqual;
	
	/**
	 * @dynamicType VidiunUserEntryExtendedStatus
	 * @var string
	 */
	public $extendedStatusIn;
	
	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new vViewHistoryUserEntryAdvancedFilter();
		
		$object_to_fill->filter = $this->getBaseFilter();
			
		return parent::toObject($object_to_fill, $props_to_skip);
	}
	
	public function getBaseFilter ()
	{
		$userEntryFilter = new VidiunViewHistoryUserEntryFilter();
		foreach ($this as $key=>$value)
		{
			$userEntryFilter->$key = $value;
		}
		
		$userEntryFilter->typeEqual = ViewHistoryPlugin::getApiValue(ViewHistoryUserEntryType::VIEW_HISTORY);
		$userEntryFilter->orderBy = VidiunUserEntryOrderBy::UPDATED_AT_DESC;
		
		return $userEntryFilter->toObject();
	}
}
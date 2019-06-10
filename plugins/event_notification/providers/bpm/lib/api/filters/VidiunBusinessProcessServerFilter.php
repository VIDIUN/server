<?php
/**
 * @package plugins.businessProcessNotification
 * @subpackage api.filters
 */
class VidiunBusinessProcessServerFilter extends VidiunBusinessProcessServerBaseFilter
{
	/**
	 * @var VidiunNullableBoolean
	 */
	public $currentDcOrExternal;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $currentDc;

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new BusinessProcessServerFilter();
	}

	/* (non-PHPdoc)
	 * @see VidiunFilter::toObject()
	 */
	public function toObject ( $object_to_fill = null, $props_to_skip = array() )
	{
		if(!$this->isNull('currentDc') && VidiunNullableBoolean::toBoolean($this->currentDc))
			$this->dcEqual = vDataCenterMgr::getCurrentDcId();

		elseif(!$this->isNull('currentDcOrExternal') && VidiunNullableBoolean::toBoolean($this->currentDcOrExternal))
		{
			$this->dcEqOrNull = vDataCenterMgr::getCurrentDcId();
		}

		return parent::toObject($object_to_fill, $props_to_skip);
	}
}

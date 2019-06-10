<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunMediaInfoFilter extends VidiunMediaInfoBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new MediaInfoFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::toObject()
	 */
	public function toObject ( $object_to_fill = null, $props_to_skip = array() )
	{
		if(!$this->flavorAssetIdEqual)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, $this->getFormattedPropertyNameWithClassName('flavorAssetIdEqual'));
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}

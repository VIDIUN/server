<?php
/**
 * @package plugins.reach
 * @subpackage api.filters
 */
class VidiunVendorCaptionsCatalogItemFilter extends VidiunVendorCaptionsCatalogItemBaseFilter
{
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if(!$type)
			$type = VidiunVendorServiceFeature::CAPTIONS;
		
		return parent::getTypeListResponse($pager, $responseProfile, $type);
	}
}

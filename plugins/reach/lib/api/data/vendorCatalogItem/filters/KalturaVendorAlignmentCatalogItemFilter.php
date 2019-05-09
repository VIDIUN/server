<?php
/**
 * @package plugins.reach
 * @subpackage api.filters
 */
class VidiunVendorAlignmentCatalogItemFilter extends VidiunVendorCaptionsCatalogItemBaseFilter
{
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if(!$type)
			$type = VidiunVendorServiceFeature::ALIGNMENT;
		
		return parent::getTypeListResponse($pager, $responseProfile, $type);
	}
}

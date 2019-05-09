<?php
/**
 * @package plugins.reach
 * @subpackage api.filters
 */
class VidiunVendorTranslationCatalogItemFilter extends VidiunVendorTranslationCatalogItemBaseFilter
{
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if(!$type)
			$type = VidiunVendorServiceFeature::TRANSLATION;
			
		return parent::getTypeListResponse($pager, $responseProfile, $type);
	}
}

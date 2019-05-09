<?php
/**
 * @package plugins.reach
 * @subpackage api.filters
 */
class VidiunVendorAudioDescriptionCatalogItemFilter extends VidiunVendorCaptionsCatalogItemBaseFilter
{
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if(!$type)
			$type = VidiunVendorServiceFeature::AUDIO_DESCRIPTION;
		
		return parent::getTypeListResponse($pager, $responseProfile, $type);
	}
}

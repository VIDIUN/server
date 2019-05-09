<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunLiveAssetFilter extends VidiunLiveAssetBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$types = VidiunPluginManager::getExtendedTypes(assetPeer::OM_CLASS, assetType::LIVE);
		return $this->getTypeListResponse($pager, $responseProfile, $types);
	}
}

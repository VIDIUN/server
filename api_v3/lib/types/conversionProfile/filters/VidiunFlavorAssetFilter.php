<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunFlavorAssetFilter extends VidiunFlavorAssetBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$types = assetPeer::retrieveAllFlavorsTypes();
		return $this->getTypeListResponse($pager, $responseProfile, $types);
	}
}

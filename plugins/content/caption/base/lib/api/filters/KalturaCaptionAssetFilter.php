<?php
/**
 * @package plugins.caption
 * @subpackage api.filters
 */
class VidiunCaptionAssetFilter extends VidiunCaptionAssetBaseFilter
{

	static private $map_between_objects = array
	(
		"captionParamsIdEqual" => "_eq_flavor_params_id",
		"captionParamsIdIn" => "_in_flavor_params_id",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}	

	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getTypeListResponse()
	 */
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager, $types);
		
		$response = new VidiunCaptionAssetListResponse();
		$response->objects = VidiunCaptionAssetArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;  
	}

	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$types = VidiunPluginManager::getExtendedTypes(assetPeer::OM_CLASS, CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION));
		return $this->getTypeListResponse($pager, $responseProfile, $types);
	}
}

<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunThumbAssetFilter extends VidiunThumbAssetBaseFilter
{	
	static private $map_between_objects = array
	(
		"thumbParamsIdEqual" => "_eq_flavor_params_id",
		"thumbParamsIdIn" => "_in_flavor_params_id",
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
		
		$response = new VidiunThumbAssetListResponse();
		$response->objects = VidiunThumbAssetArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;  
	}
	
	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$types = VidiunPluginManager::getExtendedTypes(assetPeer::OM_CLASS, assetType::THUMBNAIL);
		return $this->getTypeListResponse($pager, $responseProfile, $types);
	}
}

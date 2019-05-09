<?php
/**
 * @package plugins.attachment
 * @subpackage api.filters
 */
class VidiunAttachmentAssetFilter extends VidiunAttachmentAssetBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getTypeListResponse()
	 */
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager, $types);
		
		$response = new VidiunAttachmentAssetListResponse();
		$response->objects = VidiunAttachmentAssetArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;  
	}

	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$types = VidiunPluginManager::getExtendedTypes(assetPeer::OM_CLASS, AttachmentPlugin::getAssetTypeCoreValue(AttachmentAssetType::ATTACHMENT));
		return $this->getTypeListResponse($pager, $responseProfile, $types);
	}
}

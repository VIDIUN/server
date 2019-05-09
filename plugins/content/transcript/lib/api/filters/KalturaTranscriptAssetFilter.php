<?php
/**
 * @package plugins.transcript
 * @subpackage api.filters
 */
class VidiunTranscriptAssetFilter extends VidiunTranscriptAssetBaseFilter
{	
	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getTypeListResponse()
	 */
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		$types = VidiunPluginManager::getExtendedTypes(assetPeer::OM_CLASS, TranscriptPlugin::getAssetTypeCoreValue(TranscriptAssetType::TRANSCRIPT));
		list($list, $totalCount) = $this->doGetListResponse($pager, $types);

		$response = new VidiunTranscriptAssetListResponse();
		$response->objects = VidiunTranscriptAssetArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;
	}

	/* (non-PHPdoc)
	 * @see VidiunAssetFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$types = VidiunPluginManager::getExtendedTypes(assetPeer::OM_CLASS, TranscriptPlugin::getAssetTypeCoreValue(TranscriptAssetType::TRANSCRIPT));
		return $this->getTypeListResponse($pager, $responseProfile, $types);
	}
}

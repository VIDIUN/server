<?php
/**
 * @package plugins.externalMedia
 * @subpackage api.filters
 */
class VidiunExternalMediaEntryFilter extends VidiunExternalMediaEntryBaseFilter
{
	public function __construct()
	{
		$this->typeEqual = ExternalMediaPlugin::getEntryTypeCoreValue(ExternalMediaEntryType::EXTERNAL_MEDIA);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::toObject()
	 */
	public function toObject($coreFilter = null, $skip = array())
	{
		/* @var $coreFilter entryFilter */
		
		if($this->externalSourceTypeEqual)
		{
			$coreFilter->fields['_like_plugins_data'] = ExternalMediaPlugin::getExternalSourceSearchData($this->externalSourceTypeEqual);
			$this->externalSourceTypeEqual = null;
		}
	
		if($this->externalSourceTypeIn)
		{
			$coreExternalSourceTypes = array();
			$apiExternalSourceTypes = explode(',', $this->externalSourceTypeIn);
			foreach($apiExternalSourceTypes as $apiExternalSourceType)
			{
				$coreExternalSourceType = vPluginableEnumsManager::apiToCore('ExternalMediaSourceType', $apiExternalSourceType);
				$coreExternalSourceTypes[] = ExternalMediaPlugin::getExternalSourceSearchData($coreExternalSourceType);
			}
			$externalSourceTypeIn = implode(',', $coreExternalSourceTypes);
			
			$coreFilter->fields['_mlikeor_plugins_data'] = $externalSourceTypeIn;
			$this->externalSourceTypeIn = null;
		}
		
		return parent::toObject($coreFilter, $skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseEntryFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager);
		
	    $newList = VidiunExternalMediaEntryArray::fromDbArray($list, $responseProfile);
		$response = new VidiunExternalMediaEntryListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}

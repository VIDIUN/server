<?php

/**
 * Manage the connection between Conversion Profiles and Asset Params
 *
 * @service conversionProfileAssetParams
 * @package api
 * @subpackage services
 */
class ConversionProfileAssetParamsService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		$this->applyPartnerFilterForClass('conversionProfile2');
		$this->applyPartnerFilterForClass('assetParams');
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerGroup()
	 */
	protected function partnerGroup($peer = null) 	
	{
		if($this->actionName == 'list' && $peer == 'assetParams')
			return $this->partnerGroup . ',0';
		if($this->actionName == 'update' && $peer == 'assetParams')	
			return $this->partnerGroup . ',0';
			
		return $this->partnerGroup;
	}
	
	/**
	 * Lists asset parmas of conversion profile by ID
	 * 
	 * @action list
	 * @param VidiunConversionProfileAssetParamsFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunConversionProfileAssetParamsListResponse
	 */
	public function listAction(VidiunConversionProfileAssetParamsFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunConversionProfileAssetParamsFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * Update asset parmas of conversion profile by ID
	 * 
	 * @action update
	 * @param int $conversionProfileId
	 * @param int $assetParamsId
	 * @param VidiunConversionProfileAssetParams $conversionProfileAssetParams
	 * @return VidiunConversionProfileAssetParams
	 */
	public function updateAction($conversionProfileId, $assetParamsId, VidiunConversionProfileAssetParams $conversionProfileAssetParams)
	{
		$conversionProfile = conversionProfile2Peer::retrieveByPK($conversionProfileId);
		if(!$conversionProfile)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId);
			
		$flavorParamsConversionProfile = flavorParamsConversionProfilePeer::retrieveByFlavorParamsAndConversionProfile($assetParamsId, $conversionProfileId);
		if(!$flavorParamsConversionProfile)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ASSET_PARAMS_NOT_FOUND, $conversionProfileId, $assetParamsId);
			
		$conversionProfileAssetParams->toUpdatableObject($flavorParamsConversionProfile);
		$flavorParamsConversionProfile->save();
			
		$conversionProfileAssetParams->fromObject($flavorParamsConversionProfile, $this->getResponseProfile());
		return $conversionProfileAssetParams;
	}
}
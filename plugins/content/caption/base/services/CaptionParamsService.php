<?php

/**
 * Add & Manage Caption Params
 *
 * @service captionParams
 * @package plugins.caption
 * @subpackage api.services
 */
class CaptionParamsService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		$this->applyPartnerFilterForClass('conversionProfile2');
		$this->applyPartnerFilterForClass('asset');
		$this->applyPartnerFilterForClass('assetParamsOutput');
		$this->applyPartnerFilterForClass('assetParams');
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerGroup()
	 */
	protected function partnerGroup($peer = null)
	{
		if(
			$this->actionName == 'get' ||
			$this->actionName == 'list'
			)
			return $this->partnerGroup . ',0';
			
		return $this->partnerGroup;
	}
	
	protected function globalPartnerAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		if ($actionName === 'list') {
			return true;
		}
		return parent::globalPartnerAllowed($actionName);
	}
	
	/**
	 * Add new Caption Params
	 * 
	 * @action add
	 * @param VidiunCaptionParams $captionParams
	 * @return VidiunCaptionParams
	 */
	public function addAction(VidiunCaptionParams $captionParams)
	{
		$captionParams->validatePropertyMinLength("name", 1);
		
		$captionParamsDb = new CaptionParams();
		$captionParams->toObject($captionParamsDb);
		
		$captionParamsDb->setPartnerId($this->getPartnerId());
		$captionParamsDb->save();
		
		$captionParams->fromObject($captionParamsDb, $this->getResponseProfile());
		return $captionParams;
	}
	
	/**
	 * Get Caption Params by ID
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunCaptionParams
	 */
	public function getAction($id)
	{
		$captionParamsDb = assetParamsPeer::retrieveByPK($id);
		
		if (!$captionParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$captionParams = VidiunFlavorParamsFactory::getFlavorParamsInstance($captionParamsDb->getType());
		$captionParams->fromObject($captionParamsDb, $this->getResponseProfile());
		
		return $captionParams;
	}
	
	/**
	 * Update Caption Params by ID
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunCaptionParams $captionParams
	 * @return VidiunCaptionParams
	 */
	public function updateAction($id, VidiunCaptionParams $captionParams)
	{
		if ($captionParams->name !== null)
			$captionParams->validatePropertyMinLength("name", 1);
			
		$captionParamsDb = assetParamsPeer::retrieveByPK($id);
		if (!$captionParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$captionParams->toUpdatableObject($captionParamsDb);
		$captionParamsDb->save();
			
		$captionParams->fromObject($captionParamsDb, $this->getResponseProfile());
		return $captionParams;
	}
	
	/**
	 * Delete Caption Params by ID
	 * 
	 * @action delete
	 * @param int $id
	 */
	public function deleteAction($id)
	{
		$captionParamsDb = assetParamsPeer::retrieveByPK($id);
		if (!$captionParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$captionParamsDb->setDeletedAt(time());
		$captionParamsDb->save();
	}
	
	/**
	 * List Caption Params by filter with paging support (By default - all system default params will be listed too)
	 * 
	 * @action list
	 * @param VidiunCaptionParamsFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunCaptionParamsListResponse
	 */
	public function listAction(VidiunCaptionParamsFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunCaptionParamsFilter();
			
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}

		$types = VidiunPluginManager::getExtendedTypes(assetParamsPeer::OM_CLASS, CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION));			
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $types);
	}
}
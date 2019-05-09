<?php

/**
 * Add & Manage Thumb Params
 *
 * @service thumbParams
 * @package api
 * @subpackage services
 */
class ThumbParamsService extends VidiunBaseService
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
	 * Add new Thumb Params
	 * 
	 * @action add
	 * @param VidiunThumbParams $thumbParams
	 * @return VidiunThumbParams
	 */
	public function addAction(VidiunThumbParams $thumbParams)
	{	
		$thumbParamsDb = new thumbParams();
		$thumbParams->toInsertableObject($thumbParamsDb);
		
		$thumbParamsDb->setPartnerId($this->getPartnerId());
		$thumbParamsDb->save();
		
		$thumbParams->fromObject($thumbParamsDb, $this->getResponseProfile());
		return $thumbParams;
	}
	
	/**
	 * Get Thumb Params by ID
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunThumbParams
	 */
	public function getAction($id)
	{
		$thumbParamsDb = assetParamsPeer::retrieveByPK($id);
		
		if (!$thumbParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$thumbParams = VidiunFlavorParamsFactory::getFlavorParamsInstance($thumbParamsDb->getType());
		$thumbParams->fromObject($thumbParamsDb, $this->getResponseProfile());
		
		return $thumbParams;
	}
	
	/**
	 * Update Thumb Params by ID
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunThumbParams $thumbParams
	 * @return VidiunThumbParams
	 */
	public function updateAction($id, VidiunThumbParams $thumbParams)
	{
		$thumbParamsDb = assetParamsPeer::retrieveByPK($id);
		if (!$thumbParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$thumbParams->toUpdatableObject($thumbParamsDb);
		$thumbParamsDb->save();
			
		$thumbParams->fromObject($thumbParamsDb, $this->getResponseProfile());
		return $thumbParams;
	}
	
	/**
	 * Delete Thumb Params by ID
	 * 
	 * @action delete
	 * @param int $id
	 */
	public function deleteAction($id)
	{
		$thumbParamsDb = assetParamsPeer::retrieveByPK($id);
		if (!$thumbParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$thumbParamsDb->setDeletedAt(time());
		$thumbParamsDb->save();
	}
	
	/**
	 * List Thumb Params by filter with paging support (By default - all system default params will be listed too)
	 * 
	 * @action list
	 * @param VidiunThumbParamsFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunThumbParamsListResponse
	 */
	public function listAction(VidiunThumbParamsFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunThumbParamsFilter();
			
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}

		$types = VidiunPluginManager::getExtendedTypes(assetParamsPeer::OM_CLASS, assetType::THUMBNAIL);
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $types);
	}
	
	/**
	 * Get Thumb Params by Conversion Profile ID
	 * 
	 * @action getByConversionProfileId
	 * @param int $conversionProfileId
	 * @return VidiunThumbParamsArray
	 */
	public function getByConversionProfileIdAction($conversionProfileId)
	{
		$conversionProfileDb = conversionProfile2Peer::retrieveByPK($conversionProfileId);
		if (!$conversionProfileDb)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId);
			
		$thumbParamsConversionProfilesDb = $conversionProfileDb->getflavorParamsConversionProfilesJoinflavorParams();
		$thumbParamsDb = array();
		foreach($thumbParamsConversionProfilesDb as $item)
		{
			/* @var $item flavorParamsConversionProfile */
			$thumbParamsDb[] = $item->getassetParams();
		}
		
		$thumbParams = VidiunThumbParamsArray::fromDbArray($thumbParamsDb, $this->getResponseProfile());
		
		return $thumbParams; 
	}
}
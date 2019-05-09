<?php

/**
 * Add & Manage Flavor Params
 *
 * @service flavorParams
 * @package api
 * @subpackage services
 */
class FlavorParamsService extends VidiunBaseService
{
	
	const PROPERTY_MIN_LENGTH = 1; 
	
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
		if( $this->actionName == 'get') {
			assetParamsPeer::setIsDefaultInDefaultCriteria(false);
			return $this->partnerGroup . ',0';
		} else if ($this->actionName == 'list') {
			return $this->partnerGroup . ',0';
		}
			
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
	 * Add new Flavor Params
	 * 
	 * @action add
	 * @param VidiunFlavorParams $flavorParams
	 * @return VidiunFlavorParams
	 */
	public function addAction(VidiunFlavorParams $flavorParams)
	{
		$flavorParams->validatePropertyMinLength("name", self::PROPERTY_MIN_LENGTH);
		
		$flavorParamsDb = $flavorParams->toObject();
		
		$flavorParamsDb->setPartnerId($this->getPartnerId());
		$flavorParamsDb->save();
		
		$flavorParams = VidiunFlavorParamsFactory::getFlavorParamsInstance($flavorParamsDb->getType());
		$flavorParams->fromObject($flavorParamsDb, $this->getResponseProfile());
		return $flavorParams;
	}
	
	/**
	 * Get Flavor Params by ID
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunFlavorParams
	 */
	public function getAction($id)
	{
		$flavorParamsDb = assetParamsPeer::retrieveByPK($id);
		
		if (!$flavorParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$flavorParams = VidiunFlavorParamsFactory::getFlavorParamsInstance($flavorParamsDb->getType());
		$flavorParams->fromObject($flavorParamsDb, $this->getResponseProfile());
		
		return $flavorParams;
	}
	
	/**
	 * Update Flavor Params by ID
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunFlavorParams $flavorParams
	 * @return VidiunFlavorParams
	 */
	public function updateAction($id, VidiunFlavorParams $flavorParams)
	{
		if ($flavorParams->name !== null)
			$flavorParams->validatePropertyMinLength("name", self::PROPERTY_MIN_LENGTH);
			
		$flavorParamsDb = assetParamsPeer::retrieveByPK($id);
		if (!$flavorParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$flavorParams->toUpdatableObject($flavorParamsDb);
		$flavorParamsDb->save();
			
		$flavorParams = VidiunFlavorParamsFactory::getFlavorParamsInstance($flavorParamsDb->getType());
		$flavorParams->fromObject($flavorParamsDb, $this->getResponseProfile());
		return $flavorParams;
	}
	
	/**
	 * Delete Flavor Params by ID
	 * 
	 * @action delete
	 * @param int $id
	 */
	public function deleteAction($id)
	{
		$flavorParamsDb = assetParamsPeer::retrieveByPK($id);
		if (!$flavorParamsDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$flavorParamsDb->setDeletedAt(time());
		$flavorParamsDb->save();
	}
	
	/**
	 * List Flavor Params by filter with paging support (By default - all system default params will be listed too)
	 * 
	 * @action list
	 * @param VidiunFlavorParamsFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunFlavorParamsListResponse
	 */
	public function listAction(VidiunFlavorParamsFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunFlavorParamsFilter();
			
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}
			
		$types = assetParamsPeer::retrieveAllFlavorParamsTypes();			
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $types);
	}
	
	/**
	 * Get Flavor Params by Conversion Profile ID
	 * 
	 * @action getByConversionProfileId
	 * @param int $conversionProfileId
	 * @return VidiunFlavorParamsArray
	 */
	public function getByConversionProfileIdAction($conversionProfileId)
	{
		$conversionProfileDb = conversionProfile2Peer::retrieveByPK($conversionProfileId);
		if (!$conversionProfileDb)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId);
			
		$flavorParamsConversionProfilesDb = $conversionProfileDb->getflavorParamsConversionProfilesJoinflavorParams();
		$flavorParamsDb = array();
		foreach($flavorParamsConversionProfilesDb as $item)
		{
			/* @var $item flavorParamsConversionProfile */
			$flavorParamsDb[] = $item->getassetParams();
		}
		
		$flavorParams = VidiunFlavorParamsArray::fromDbArray($flavorParamsDb, $this->getResponseProfile());
		
		return $flavorParams; 
	}
}
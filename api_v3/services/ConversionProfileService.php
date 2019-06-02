<?php

/**
 * Add & Manage Conversion Profiles
 *
 * @service conversionProfile
 * @package api
 * @subpackage services
 */
class ConversionProfileService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('asset');
		$this->applyPartnerFilterForClass('assetParamsOutput');
		$this->applyPartnerFilterForClass('conversionProfile2');
		$this->applyPartnerFilterForClass('assetParams');
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerGroup()
	 */
	protected function partnerGroup($peer = null)
	{
		if(
			$this->actionName == 'add' ||
			$this->actionName == 'update'
			)
		{
			assetParamsPeer::setIsDefaultInDefaultCriteria(false);
			return $this->partnerGroup . ',0';
		}
		
		return parent::partnerGroup();
	}
	
	/**
	 * Set Conversion Profile to be the partner default
	 * 
	 * @action setAsDefault
	 * @param int $id
	 * @return VidiunConversionProfile
	 * 
	 * @throws VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND
	 */
	public function setAsDefaultAction($id)
	{
		$conversionProfileDb = conversionProfile2Peer::retrieveByPK($id);
		if (!$conversionProfileDb || $conversionProfileDb->getPartnerId() != $this->getPartnerId())
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $id);
			
		$partner = $this->getPartner();
		
		if($conversionProfileDb->getType() == ConversionProfileType::MEDIA)
			$partner->setDefaultConversionProfileId($id);
		
		if($conversionProfileDb->getType() == ConversionProfileType::LIVE_STREAM)
			$partner->setDefaultLiveConversionProfileId($id);
			
		$partner->save();
		PartnerPeer::removePartnerFromCache($partner->getId());
		
		$conversionProfile = new VidiunConversionProfile();
		$conversionProfile->fromObject($conversionProfileDb, $this->getResponseProfile());
		$conversionProfile->loadFlavorParamsIds($conversionProfileDb);
		
		return $conversionProfile;
	}
	
	/**
	 * Get the partner's default conversion profile
	 * 
	 * @param VidiunConversionProfileType $type
	 * @action getDefault
	 * @return VidiunConversionProfile
	 */
	public function getDefaultAction($type = null)
	{
		if(is_null($type) || $type == VidiunConversionProfileType::MEDIA)
			$defaultProfileId = $this->getPartner()->getDefaultConversionProfileId();
		elseif($type == VidiunConversionProfileType::LIVE_STREAM)
			$defaultProfileId = $this->getPartner()->getDefaultLiveConversionProfileId();
			
		return $this->getAction($defaultProfileId);
	}
	
	/**
	 * Add new Conversion Profile
	 * 
	 * @action add
	 * @param VidiunConversionProfile $conversionProfile
	 * @return VidiunConversionProfile
	 * 
	 * @throws VidiunErrors::ASSET_PARAMS_INVALID_TYPE
	 */
	public function addAction(VidiunConversionProfile $conversionProfile)
	{
		$conversionProfileDb = $conversionProfile->toInsertableObject(new conversionProfile2());

		$conversionProfileDb->setInputTagsMap(flavorParams::TAG_WEB . ',' . flavorParams::TAG_SLWEB);
		$conversionProfileDb->setPartnerId($this->getPartnerId());
		
		if($conversionProfile->xslTransformation)
			$conversionProfileDb->incrementXslVersion();
		
		if($conversionProfile->mediaInfoXslTransformation)
			$conversionProfileDb->incrementMediaInfoXslVersion();
			
		$conversionProfileDb->save();
		
		$flavorParamsArray = $conversionProfile->getFlavorParamsAsArray();
		if ( ! empty( $flavorParamsArray ) )
		{
			$this->addFlavorParamsRelation($conversionProfileDb, $flavorParamsArray);
		}
		
		if($conversionProfile->xslTransformation)
		{
			$xsl = html_entity_decode($conversionProfile->xslTransformation);
			$key = $conversionProfileDb->getSyncKey(conversionProfile2::FILE_SYNC_MRSS_XSL);
			vFileSyncUtils::file_put_contents($key, $xsl);
		}
		
		if($conversionProfile->mediaInfoXslTransformation)
		{
			$xsl = html_entity_decode($conversionProfile->mediaInfoXslTransformation);
			$key = $conversionProfileDb->getSyncKey(conversionProfile2::FILE_SYNC_MEDIAINFO_XSL);
			vFileSyncUtils::file_put_contents($key, $xsl);
		}
		
		$conversionProfile->fromObject($conversionProfileDb, $this->getResponseProfile());
		
		// load flavor params id with the same connection (master connection) that was used for insert
		$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_MASTER);
		$conversionProfile->loadFlavorParamsIds($conversionProfileDb, $con);
		return $conversionProfile;
	}
	
	/**
	 * Get Conversion Profile by ID
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunConversionProfile
	 * 
	 * @throws VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND
	 */
	public function getAction($id)
	{
		$conversionProfileDb = conversionProfile2Peer::retrieveByPK($id);
		if (!$conversionProfileDb)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $id);
			
		$conversionProfile = new VidiunConversionProfile();
		$conversionProfile->fromObject($conversionProfileDb, $this->getResponseProfile());
		$conversionProfile->loadFlavorParamsIds($conversionProfileDb);
		
		return $conversionProfile;
	}
	
	/**
	 * Update Conversion Profile by ID
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunConversionProfile $conversionProfile
	 * @return VidiunConversionProfile
	 * 
	 * @throws VidiunErrors::ASSET_PARAMS_INVALID_TYPE
	 * @throws VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND
	 */
	public function updateAction($id, VidiunConversionProfile $conversionProfile)
	{
		$conversionProfileDb = conversionProfile2Peer::retrieveByPK($id);
		if (!$conversionProfileDb)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $id);
			
		$conversionProfile->toUpdatableObject($conversionProfileDb);
		$conversionProfileDb->setCreationMode(conversionProfile2::CONVERSION_PROFILE_2_CREATION_MODE_VMC);
		
		if($conversionProfile->xslTransformation)
			$conversionProfileDb->incrementXslVersion();
		
		if($conversionProfile->mediaInfoXslTransformation)
			$conversionProfileDb->incrementMediaInfoXslVersion();
			
		$conversionProfileDb->save();
		
		if ($conversionProfile->flavorParamsIds !== null) 
		{
			$this->deleteFlavorParamsRelation($conversionProfileDb, $conversionProfile->flavorParamsIds);
			$flavorParamsArray = $conversionProfile->getFlavorParamsAsArray();
			if ( ! empty( $flavorParamsArray ) )
			{
				$this->addFlavorParamsRelation($conversionProfileDb, $flavorParamsArray);
			}
		}
		
		if($conversionProfile->xslTransformation)
		{
			$xsl = html_entity_decode($conversionProfile->xslTransformation);
			$key = $conversionProfileDb->getSyncKey(conversionProfile2::FILE_SYNC_MRSS_XSL);
			vFileSyncUtils::file_put_contents($key, $xsl);
		}
		
		if($conversionProfile->mediaInfoXslTransformation)
		{
			$xsl = html_entity_decode($conversionProfile->mediaInfoXslTransformation);
			$key = $conversionProfileDb->getSyncKey(conversionProfile2::FILE_SYNC_MEDIAINFO_XSL);
			vFileSyncUtils::file_put_contents($key, $xsl);
		}
		
		$conversionProfile->fromObject($conversionProfileDb, $this->getResponseProfile());
		// load flavor params id with the same connection (master connection) that was used for insert
		$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_MASTER);
		$conversionProfile->loadFlavorParamsIds($conversionProfileDb, $con);
		
		return $conversionProfile;
	}
	
	/**
	 * Delete Conversion Profile by ID
	 * 
	 * @action delete
	 * @param int $id
	 * 
	 * @throws VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::CANNOT_DELETE_DEFAULT_CONVERSION_PROFILE
	 */
	public function deleteAction($id)
	{
		$conversionProfileDb = conversionProfile2Peer::retrieveByPK($id);
		if (!$conversionProfileDb)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $id);
			
		if ($conversionProfileDb->getIsDefault() === true)
			throw new VidiunAPIException(VidiunErrors::CANNOT_DELETE_DEFAULT_CONVERSION_PROFILE);
			
		$this->deleteFlavorParamsRelation($conversionProfileDb);
		
		$conversionProfileDb->setDeletedAt(time());
		$conversionProfileDb->save();
	}
	
	/**
	 * List Conversion Profiles by filter with paging support
	 * 
	 * @action list
	 * @param VidiunConversionProfileFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunConversionProfileListResponse
	 */
	public function listAction(VidiunConversionProfileFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunConversionProfileFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());  
	}
	
	/**
	 * Adds the relation of flavorParams <> conversionProfile2
	 * 
	 * @param conversionProfile2 $conversionProfileDb
	 * @param $flavorParamsIds
	 * 
	 * @throws VidiunErrors::ASSET_PARAMS_INVALID_TYPE
	 */
	protected function addFlavorParamsRelation(conversionProfile2 $conversionProfileDb, $flavorParamsIds)
	{
		$existingIds = flavorParamsConversionProfilePeer::getFlavorIdsByProfileId($conversionProfileDb->getId());
		
		$assetParamsObjects = assetParamsPeer::retrieveByPKs($flavorParamsIds);
		foreach($assetParamsObjects as $assetParams)
		{
			/* @var $assetParams assetParams */
			if(in_array($assetParams->getId(), $existingIds))
				continue;
				
			$fpc = new flavorParamsConversionProfile();
			$fpc->setConversionProfileId($conversionProfileDb->getId());
			$fpc->setFlavorParamsId($assetParams->getId());
			$fpc->setReadyBehavior($assetParams->getReadyBehavior());
			$fpc->setSystemName($assetParams->getSystemName());
			$fpc->setForceNoneComplied(false);
			
			if($assetParams->hasTag(assetParams::TAG_SOURCE) || $assetParams->hasTag(assetParams::TAG_INGEST))
				$fpc->setOrigin(assetParamsOrigin::INGEST);
			else
				$fpc->setOrigin(assetParamsOrigin::CONVERT);
			
			$fpc->save();
		}
	}
	
	/**
	 * Delete the relation of flavorParams <> conversionProfile2
	 * 
	 * @param conversionProfile2 $conversionProfileDb
	 * @param string|array $notInFlavorIds comma separated ID[s] that should not be deleted
	 */
	protected function deleteFlavorParamsRelation(conversionProfile2 $conversionProfileDb, $notInFlavorIds = null)
	{
		$c = new Criteria();
		$c->add(flavorParamsConversionProfilePeer::CONVERSION_PROFILE_ID, $conversionProfileDb->getId());
		if($notInFlavorIds)
		{
			if(!is_array($notInFlavorIds))
				$notInFlavorIds = explode(',', $notInFlavorIds);
				
			$c->add(flavorParamsConversionProfilePeer::FLAVOR_PARAMS_ID, $notInFlavorIds, Criteria::NOT_IN);
		}
			
		$flavorParamsConversionProfiles = flavorParamsConversionProfilePeer::doSelect($c);
		
		foreach($flavorParamsConversionProfiles as $flavorParamsConversionProfile)
		{
			/* @var $flavorParamsConversionProfile flavorParamsConversionProfile */ 
			$flavorParamsConversionProfile->delete();
		}
	}
}

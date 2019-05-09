<?php
/**
 * Virus scan profile service
 *
 * @service virusScanProfile
 * @package plugins.virusScan
 * @subpackage api.services
 */
class VirusScanProfileService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if($this->getPartnerId() != Partner::ADMIN_CONSOLE_PARTNER_ID)
		{
			$this->applyPartnerFilterForClass('VirusScanProfile');
			$this->applyPartnerFilterForClass('asset');
		}
		
		if(!VirusScanPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, VirusScanPlugin::PLUGIN_NAME);
	}
	
	/**
	 * List virus scan profile objects by filter and pager
	 * 
	 * @action list
	 * @param VidiunVirusScanProfileFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunVirusScanProfileListResponse
	 */
	function listAction(VidiunVirusScanProfileFilter  $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunVirusScanProfileFilter;
			
		$virusScanProfileFilter = $filter->toObject();
		
		$c = new Criteria();
		$virusScanProfileFilter->attachToCriteria($c);
		$count = VirusScanProfilePeer::doCount($c);
		
		if (! $pager)
			$pager = new VidiunFilterPager ();
		$pager->attachToCriteria ( $c );
		$list = VirusScanProfilePeer::doSelect($c);
		
		$response = new VidiunVirusScanProfileListResponse();
		$response->objects = VidiunVirusScanProfileArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
		
		return $response;
	}
	
	/**
	 * Allows you to add an virus scan profile object and virus scan profile content associated with Vidiun object
	 * 
	 * @action add
	 * @param VidiunVirusScanProfile $virusScanProfile
	 * @return VidiunVirusScanProfile
	 */
	function addAction(VidiunVirusScanProfile $virusScanProfile)
	{
		$virusScanProfile->validatePropertyNotNull("engineType");
		$virusScanProfile->validatePropertyNotNull("actionIfInfected");
		$virusScanProfile->validatePropertyMaxLength("name", 30);
		
		if(!$virusScanProfile->name)
			$virusScanProfile->name = time();
			
		if(!$virusScanProfile->status)
			$virusScanProfile->status = VidiunVirusScanProfileStatus::DISABLED;
			
		$dbVirusScanProfile = $virusScanProfile->toInsertableObject();
		$dbVirusScanProfile->setPartnerId($this->getPartnerId());
		$dbVirusScanProfile->save();
		
		$virusScanProfile = new VidiunVirusScanProfile();
		$virusScanProfile->fromObject($dbVirusScanProfile, $this->getResponseProfile());
		
		return $virusScanProfile;
	}
	
	/**
	 * Retrieve an virus scan profile object by id
	 * 
	 * @action get
	 * @param int $virusScanProfileId 
	 * @return VidiunVirusScanProfile
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	function getAction($virusScanProfileId)
	{
		$dbVirusScanProfile = VirusScanProfilePeer::retrieveByPK( $virusScanProfileId );
		
		if(!$dbVirusScanProfile)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $virusScanProfileId);
			
		$virusScanProfile = new VidiunVirusScanProfile();
		$virusScanProfile->fromObject($dbVirusScanProfile, $this->getResponseProfile());
		
		return $virusScanProfile;
	}


	/**
	 * Update existing virus scan profile, it is possible to update the virus scan profile id too
	 * 
	 * @action update
	 * @param int $virusScanProfileId
	 * @param VidiunVirusScanProfile $virusScanProfile
	 * @return VidiunVirusScanProfile
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */	
	function updateAction($virusScanProfileId, VidiunVirusScanProfile $virusScanProfile)
	{
		$dbVirusScanProfile = VirusScanProfilePeer::retrieveByPK($virusScanProfileId);
	
		if (!$dbVirusScanProfile)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $virusScanProfileId);
		
		$dbVirusScanProfile = $virusScanProfile->toUpdatableObject($dbVirusScanProfile);
		$dbVirusScanProfile->save();
	
		$virusScanProfile->fromObject($dbVirusScanProfile, $this->getResponseProfile());
		
		return $virusScanProfile;
	}

	/**
	 * Mark the virus scan profile as deleted
	 * 
	 * @action delete
	 * @param int $virusScanProfileId 
	 * @return VidiunVirusScanProfile
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	function deleteAction($virusScanProfileId)
	{
		$dbVirusScanProfile = VirusScanProfilePeer::retrieveByPK($virusScanProfileId);
	
		if (!$dbVirusScanProfile)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $virusScanProfileId);
		
		$dbVirusScanProfile->setStatus(VidiunVirusScanProfileStatus::DELETED);
		$dbVirusScanProfile->save();
			
		$virusScanProfile = new VidiunVirusScanProfile();
		$virusScanProfile->fromObject($dbVirusScanProfile, $this->getResponseProfile());
		
		return $virusScanProfile;
	}

	/**
	 * Scan flavor asset according to virus scan profile
	 * 
	 * @action scan
	 * @param int $virusScanProfileId
	 * @param string $flavorAssetId 
	 * @return int job id
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @throws VidiunErrors::INVALID_FLAVOR_ASSET_ID
	 * @throws VidiunErrors::INVALID_FILE_SYNC_ID
	 */		
	function scanAction($flavorAssetId, $virusScanProfileId = null)
	{
		$dbFlavorAsset = assetPeer::retrieveById($flavorAssetId);
		if (!$dbFlavorAsset)
			throw new VidiunAPIException(VidiunErrors::INVALID_FLAVOR_ASSET_ID, $flavorAssetId);
		
		if ($virusScanProfileId)
		{
			$dbVirusScanProfile = VirusScanProfilePeer::retrieveByPK($virusScanProfileId);
		}
		else
		{
			$dbVirusScanProfile = VirusScanProfilePeer::getSuitableProfile($dbFlavorAsset->getEntryId());
		}
		if (!$dbVirusScanProfile)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $virusScanProfileId);
			
		$syncKey = $dbFlavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$srcFilePath = vFileSyncUtils::getLocalFilePathForKey($syncKey);
		if(!$srcFilePath)
			throw new VidiunAPIException(VidiunErrors::INVALID_FILE_SYNC_ID, $syncKey);
			
		$job = vVirusScanJobsManager::addVirusScanJob(null, $dbFlavorAsset->getPartnerId(), $dbFlavorAsset->getEntryId(), $dbFlavorAsset->getId(), $syncKey, $dbVirusScanProfile->getEngineType(), $dbVirusScanProfile->getActionIfInfected());
		return $job->getId();
	}
}

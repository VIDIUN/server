<?php
/**
 * 
 * @service drmProfile
 * @package plugins.drm
 * @subpackage api.services
 */
class DrmProfileService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('DrmProfile');
		
		if (!DrmPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, DrmPlugin::PLUGIN_NAME);		
	}
	
	/**
	 * Allows you to add a new DrmProfile object
	 * 
	 * @action add
	 * @param VidiunDrmProfile $drmProfile
	 * @return VidiunDrmProfile
	 * 
	 * @throws VidiunErrors::PLUGIN_NOT_AVAILABLE_FOR_PARTNER
	 * @throws VidiunErrors::INVALID_PARTNER_ID
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws DrmErrors::ACTIVE_PROVIDER_PROFILE_ALREADY_EXIST
	 */
	public function addAction(VidiunDrmProfile $drmProfile)
	{
		// check for required parameters
		$drmProfile->validatePropertyNotNull('name');
		$drmProfile->validatePropertyNotNull('status');
		$drmProfile->validatePropertyNotNull('provider');
		$drmProfile->validatePropertyNotNull('partnerId');
		
		// validate values						
		if (!PartnerPeer::retrieveByPK($drmProfile->partnerId)) {
			throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $drmProfile->partnerId);
		}
		
		if (!DrmPlugin::isAllowedPartner($drmProfile->partnerId))
		{
			throw new VidiunAPIException(VidiunErrors::PLUGIN_NOT_AVAILABLE_FOR_PARTNER, DrmPlugin::getPluginName(), $drmProfile->partnerId);
		}
		
		$dbDrmProfile = $drmProfile->toInsertableObject();
		
		if(DrmProfilePeer::retrieveByProvider($dbDrmProfile->getProvider()))
		{
			throw new VidiunAPIException(DrmErrors::ACTIVE_PROVIDER_PROFILE_ALREADY_EXIST, $drmProfile->provider);
		}

		// save in database
		
		$dbDrmProfile->save();
		
		// return the saved object
		$drmProfile = VidiunDrmProfile::getInstanceByType($dbDrmProfile->getProvider());
		$drmProfile->fromObject($dbDrmProfile, $this->getResponseProfile());
		return $drmProfile;		
	}
	
	/**
	 * Retrieve a VidiunDrmProfile object by ID
	 * 
	 * @action get
	 * @param int $drmProfileId 
	 * @return VidiunDrmProfile
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function getAction($drmProfileId)
	{
		$dbDrmProfile = DrmProfilePeer::retrieveByPK($drmProfileId);
		
		if (!$dbDrmProfile) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $drmProfileId);
		}
		$drmProfile = VidiunDrmProfile::getInstanceByType($dbDrmProfile->getProvider());
		$drmProfile->fromObject($dbDrmProfile, $this->getResponseProfile());
		
		return $drmProfile;
	}
	

	/**
	 * Update an existing VidiunDrmProfile object
	 * 
	 * @action update
	 * @param int $drmProfileId
	 * @param VidiunDrmProfile $drmProfile
	 * @return VidiunDrmProfile
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */	
	public function updateAction($drmProfileId, VidiunDrmProfile $drmProfile)
	{
		$dbDrmProfile = DrmProfilePeer::retrieveByPK($drmProfileId);
		
		if (!$dbDrmProfile) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $drmProfileId);
		}
								
		$dbDrmProfile = $drmProfile->toUpdatableObject($dbDrmProfile);
		$dbDrmProfile->save();
			
		$drmProfile = VidiunDrmProfile::getInstanceByType($dbDrmProfile->getProvider());
		$drmProfile->fromObject($dbDrmProfile, $this->getResponseProfile());
		
		return $drmProfile;
	}

	/**
	 * Mark the VidiunDrmProfile object as deleted
	 * 
	 * @action delete
	 * @param int $drmProfileId 
	 * @return VidiunDrmProfile
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function deleteAction($drmProfileId)
	{
		$dbDrmProfile = DrmProfilePeer::retrieveByPK($drmProfileId);
		
		if (!$dbDrmProfile) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $drmProfileId);
		}

		$dbDrmProfile->setStatus(DrmProfileStatus::DELETED);
		$dbDrmProfile->save();
			
		$drmProfile = VidiunDrmProfile::getInstanceByType($dbDrmProfile->getProvider());
		$drmProfile->fromObject($dbDrmProfile, $this->getResponseProfile());
		
		return $drmProfile;
	}
	
	/**
	 * List VidiunDrmProfile objects
	 * 
	 * @action list
	 * @param VidiunDrmProfileFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunDrmProfileListResponse
	 */
	public function listAction(VidiunDrmProfileFilter  $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunDrmProfileFilter();

		$drmProfileFilter = $filter->toObject();
		$c = new Criteria();
		$drmProfileFilter->attachToCriteria($c);
		$count = DrmProfilePeer::doCount($c);
		if (! $pager)
			$pager = new VidiunFilterPager ();
		$pager->attachToCriteria ( $c );
		$list = DrmProfilePeer::doSelect($c);
		
		$response = new VidiunDrmProfileListResponse();
		$response->objects = VidiunDrmProfileArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
		
		return $response;
	}
	
	/**
	 * Retrieve a VidiunDrmProfile object by provider, if no specific profile defined return default profile
	 * 
	 * @action getByProvider
	 * @param VidiunDrmProviderType $provider
	 * @return VidiunDrmProfile
	 */
	public function getByProviderAction($provider)
	{	
		$drmProfile = VidiunDrmProfile::getInstanceByType($provider);
		$drmProfile->provider = $provider;
		$tmpDbProfile = $drmProfile->toObject();
			
		$dbDrmProfile = DrmProfilePeer::retrieveByProvider($tmpDbProfile->getProvider());
		if(!$dbDrmProfile)
		{
            if ($provider == VidiunDrmProviderType::CENC)
            {
                $dbDrmProfile = new DrmProfile();
            }
            else
            {
                $dbDrmProfile = VidiunPluginManager::loadObject('DrmProfile', $tmpDbProfile->getProvider());
            }
			$dbDrmProfile->setName('default');
			$dbDrmProfile->setProvider($tmpDbProfile->getProvider());
		}		
		$drmProfile->fromObject($dbDrmProfile, $this->getResponseProfile());

		return $drmProfile;
	}
}

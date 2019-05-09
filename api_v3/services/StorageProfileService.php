<?php
/**
 * The Storage Profile service allows you to export your Vidiun content to external storage volumes.
 * This service is disabled by default, please contact your account manager if you wish to enable it for your partner.
 *
 * @service storageProfile
 * @package api
 * @subpackage services
 */
class StorageProfileService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		$partnerId = $this->getPartnerId();
		if(!$this->getPartner()->getEnabledService(PermissionName::FEATURE_REMOTE_STORAGE))
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
			
		$this->applyPartnerFilterForClass('StorageProfile');
	}
	
	/**
	 * Adds a storage profile to the Vidiun DB.
	 *
	 * @action add
	 * @param VidiunStorageProfile $storageProfile 
	 * @return VidiunStorageProfile
	 */
	function addAction(VidiunStorageProfile $storageProfile)
	{
		if(!$storageProfile->status)
			$storageProfile->status = VidiunStorageProfileStatus::DISABLED;
			
		$dbStorageProfile = $storageProfile->toInsertableObject();
		/* @var $dbStorageProfile StorageProfile */
		$dbStorageProfile->setPartnerId($this->impersonatedPartnerId);
		$dbStorageProfile->save();
		
		$storageProfile = VidiunStorageProfile::getInstanceByType($dbStorageProfile->getProtocol());
				
		$storageProfile->fromObject($dbStorageProfile, $this->getResponseProfile());
		return $storageProfile;
	}
		
	/**
	 * @action updateStatus
	 * @param int $storageId
	 * @param VidiunStorageProfileStatus $status
	 */
	public function updateStatusAction($storageId, $status)
	{
		$dbStorage = StorageProfilePeer::retrieveByPK($storageId);
		if (!$dbStorage)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $storageId);
			
		$dbStorage->setStatus($status);
		$dbStorage->save();
	}	
	
	/**
	 * Get storage profile by id
	 * 
	 * @action get
	 * @param int $storageProfileId
	 * @return VidiunStorageProfile
	 */
	function getAction($storageProfileId)
	{
		$dbStorageProfile = StorageProfilePeer::retrieveByPK($storageProfileId);
		if (!$dbStorageProfile)
			return null;

		$protocol = $dbStorageProfile->getProtocol();
		$storageProfile = VidiunStorageProfile::getInstanceByType($protocol);
		
		$storageProfile->fromObject($dbStorageProfile, $this->getResponseProfile());
		return $storageProfile;
	}
	
	/**
	 * Update storage profile by id 
	 * 
	 * @action update
	 * @param int $storageProfileId
	 * @param VidiunStorageProfile $storageProfile
	 * @return VidiunStorageProfile
	 */
	function updateAction($storageProfileId, VidiunStorageProfile $storageProfile)
	{
		$dbStorageProfile = StorageProfilePeer::retrieveByPK($storageProfileId);
		if (!$dbStorageProfile)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $storageProfileId);
			
		$dbStorageProfile = $storageProfile->toUpdatableObject($dbStorageProfile);
		$dbStorageProfile->save();
		
		$protocol = $dbStorageProfile->getProtocol();
		$storageProfile = VidiunStorageProfile::getInstanceByType($protocol);
		
		$storageProfile->fromObject($dbStorageProfile, $this->getResponseProfile());
		return $storageProfile;
	}
	
	/**	
	 * @action list
	 * @param VidiunStorageProfileFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunStorageProfileListResponse
	 */
	public function listAction(VidiunStorageProfileFilter $filter = null, VidiunFilterPager $pager = null)
	{
		$c = new Criteria();
		
		if (!$filter)
			$filter = new VidiunStorageProfileFilter();
		
		$storageProfileFilter = new StorageProfileFilter();
		$filter->toObject($storageProfileFilter);
		$storageProfileFilter->attachToCriteria($c);
		$list = StorageProfilePeer::doSelect($c);
			
		if (!$pager)
			$pager = new VidiunFilterPager();
			
		$pager->attachToCriteria($c);
		
		$response = new VidiunStorageProfileListResponse();
		$response->totalCount = StorageProfilePeer::doCount($c);
		$response->objects = VidiunStorageProfileArray::fromDbArray($list, $this->getResponseProfile());
		return $response;
	}
	
}

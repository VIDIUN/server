<?php
/**
 * 
 * @service drmPolicy
 * @package plugins.drm
 * @subpackage api.services
 */
class DrmPolicyService extends VidiunBaseService
{	
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		if (!DrmPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
			
		$this->applyPartnerFilterForClass('DrmPolicy');
	}
	
	/**
	 * Allows you to add a new DrmPolicy object
	 * 
	 * @action add
	 * @param VidiunDrmPolicy $drmPolicy
	 * @return VidiunDrmPolicy
	 * 
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 */
	public function addAction(VidiunDrmPolicy $drmPolicy)
	{
		// check for required parameters
		$drmPolicy->validatePropertyNotNull('name');
		$drmPolicy->validatePropertyNotNull('status');
		$drmPolicy->validatePropertyNotNull('provider');
		$drmPolicy->validatePropertyNotNull('systemName');
		$drmPolicy->validatePropertyNotNull('scenario');
		$drmPolicy->validatePropertyNotNull('partnerId');
		
		// validate values
		$drmPolicy->validatePolicy();
						
		if (!PartnerPeer::retrieveByPK($drmPolicy->partnerId)) {
			throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $drmPolicy->partnerId);
		}
		
		if (!DrmPlugin::isAllowedPartner($drmPolicy->partnerId))
		{
			throw new VidiunAPIException(VidiunErrors::PLUGIN_NOT_AVAILABLE_FOR_PARTNER, DrmPlugin::getPluginName(), $drmPolicy->partnerId);
		}

		if(DrmPolicyPeer::retrieveBySystemName($drmPolicy->systemName))
		{
			throw new VidiunAPIException(DrmErrors::DRM_POLICY_DUPLICATE_SYSTEM_NAME, $drmPolicy->systemName);
		}
				
		// save in database
		$dbDrmPolicy = $drmPolicy->toInsertableObject();
		$dbDrmPolicy->save();
		
		// return the saved object
		$drmPolicy = VidiunDrmPolicy::getInstanceByType($dbDrmPolicy->getProvider());
		$drmPolicy->fromObject($dbDrmPolicy, $this->getResponseProfile());
		return $drmPolicy;
		
	}
	
	/**
	 * Retrieve a VidiunDrmPolicy object by ID
	 * 
	 * @action get
	 * @param int $drmPolicyId 
	 * @return VidiunDrmPolicy
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function getAction($drmPolicyId)
	{
		$dbDrmPolicy = DrmPolicyPeer::retrieveByPK($drmPolicyId);
		
		if (!$dbDrmPolicy) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $drmPolicyId);
		}
			
		$drmPolicy = VidiunDrmPolicy::getInstanceByType($dbDrmPolicy->getProvider());
		$drmPolicy->fromObject($dbDrmPolicy, $this->getResponseProfile());
		
		return $drmPolicy;
	}
	

	/**
	 * Update an existing VidiunDrmPolicy object
	 * 
	 * @action update
	 * @param int $drmPolicyId
	 * @param VidiunDrmPolicy $drmPolicy
	 * @return VidiunDrmPolicy
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */	
	public function updateAction($drmPolicyId, VidiunDrmPolicy $drmPolicy)
	{
		$dbDrmPolicy = DrmPolicyPeer::retrieveByPK($drmPolicyId);
		
		if (!$dbDrmPolicy) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $drmPolicyId);
		}
		
		$drmPolicy->validatePolicy();
						
		$dbDrmPolicy = $drmPolicy->toUpdatableObject($dbDrmPolicy);
		$dbDrmPolicy->save();
	
		$drmPolicy = VidiunDrmPolicy::getInstanceByType($dbDrmPolicy->getProvider());
		$drmPolicy->fromObject($dbDrmPolicy, $this->getResponseProfile());
		
		return $drmPolicy;
	}

	/**
	 * Mark the VidiunDrmPolicy object as deleted
	 * 
	 * @action delete
	 * @param int $drmPolicyId 
	 * @return VidiunDrmPolicy
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function deleteAction($drmPolicyId)
	{
		$dbDrmPolicy = DrmPolicyPeer::retrieveByPK($drmPolicyId);
		
		if (!$dbDrmPolicy) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $drmPolicyId);
		}

		$dbDrmPolicy->setStatus(DrmPolicyStatus::DELETED);
		$dbDrmPolicy->save();
			
		$drmPolicy = VidiunDrmPolicy::getInstanceByType($dbDrmPolicy->getProvider());
		$drmPolicy->fromObject($dbDrmPolicy, $this->getResponseProfile());
		
		return $drmPolicy;
	}
	
	/**
	 * List VidiunDrmPolicy objects
	 * 
	 * @action list
	 * @param VidiunDrmPolicyFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunDrmPolicyListResponse
	 */
	public function listAction(VidiunDrmPolicyFilter  $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunDrmPolicyFilter();
			
		$drmPolicyFilter = $filter->toObject();

		$c = new Criteria();
		$drmPolicyFilter->attachToCriteria($c);
		$count = DrmPolicyPeer::doCount($c);		
		if (! $pager)
			$pager = new VidiunFilterPager ();
		$pager->attachToCriteria ( $c );
		$list = DrmPolicyPeer::doSelect($c);
		
		$response = new VidiunDrmPolicyListResponse();
		$response->objects = VidiunDrmPolicyArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
		
		return $response;
	}

}

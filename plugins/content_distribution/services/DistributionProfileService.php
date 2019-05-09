<?php
/**
 * Distribution Profile service
 *
 * @service distributionProfile
 * @package plugins.contentDistribution
 * @subpackage api.services
 */
class DistributionProfileService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		$this->applyPartnerFilterForClass('DistributionProfile');
		
		if(!ContentDistributionPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, ContentDistributionPlugin::PLUGIN_NAME);
	}
	
	/**
	 * Add new Distribution Profile
	 * 
	 * @action add
	 * @param VidiunDistributionProfile $distributionProfile
	 * @return VidiunDistributionProfile
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROVIDER_NOT_FOUND
	 */
	function addAction(VidiunDistributionProfile $distributionProfile)
	{
		$distributionProfile->validatePropertyMinLength("name", 1);
		$distributionProfile->validatePropertyNotNull("providerType");
					
		if(is_null($distributionProfile->status))
			$distributionProfile->status = VidiunDistributionProfileStatus::DISABLED;
		
		$providerType = vPluginableEnumsManager::apiToCore('DistributionProviderType', $distributionProfile->providerType);
		$dbDistributionProfile = DistributionProfilePeer::createDistributionProfile($providerType);
		if(!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROVIDER_NOT_FOUND, $distributionProfile->providerType);
			
		$distributionProfile->toInsertableObject($dbDistributionProfile);
		$dbDistributionProfile->setPartnerId($this->impersonatedPartnerId);
		$dbDistributionProfile->save();
		
		$distributionProfile = VidiunDistributionProfileFactory::createVidiunDistributionProfile($dbDistributionProfile->getProviderType());
		$distributionProfile->fromObject($dbDistributionProfile, $this->getResponseProfile());
		return $distributionProfile;
	}
	
	/**
	 * Get Distribution Profile by id
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunDistributionProfile
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($id);
		if (!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $id);
			
		$distributionProfile = VidiunDistributionProfileFactory::createVidiunDistributionProfile($dbDistributionProfile->getProviderType());
		$distributionProfile->fromObject($dbDistributionProfile, $this->getResponseProfile());
		return $distributionProfile;
	}
	
	/**
	 * Update Distribution Profile by id
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunDistributionProfile $distributionProfile
	 * @return VidiunDistributionProfile
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 */
	function updateAction($id, VidiunDistributionProfile $distributionProfile)
	{
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($id);
		if (!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $id);
		
		if ($distributionProfile->name !== null)
			$distributionProfile->validatePropertyMinLength("name", 1);
			
		$distributionProfile->toUpdatableObject($dbDistributionProfile);
		$dbDistributionProfile->save();
		
		$distributionProfile = VidiunDistributionProfileFactory::createVidiunDistributionProfile($dbDistributionProfile->getProviderType());
		$distributionProfile->fromObject($dbDistributionProfile, $this->getResponseProfile());
		return $distributionProfile;
	}
	
	/**
	 * Update Distribution Profile status by id
	 * 
	 * @action updateStatus
	 * @param int $id
	 * @param VidiunDistributionProfileStatus $status
	 * @return VidiunDistributionProfile
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 */
	function updateStatusAction($id, $status)
	{
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($id);
		if (!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $id);
		
		$dbDistributionProfile->setStatus($status);
		$dbDistributionProfile->save();
		
		$distributionProfile = VidiunDistributionProfileFactory::createVidiunDistributionProfile($dbDistributionProfile->getProviderType());
		$distributionProfile->fromObject($dbDistributionProfile, $this->getResponseProfile());
		return $distributionProfile;
	}
	
	/**
	 * Delete Distribution Profile by id
	 * 
	 * @action delete
	 * @param int $id
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 */
	function deleteAction($id)
	{
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($id);
		if (!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $id);

		$dbDistributionProfile->setStatus(DistributionProfileStatus::DELETED);
		$dbDistributionProfile->save();
	}
	
	
	/**
	 * List all distribution providers
	 * 
	 * @action list
	 * @param VidiunDistributionProfileFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunDistributionProfileListResponse
	 */
	function listAction(VidiunDistributionProfileFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunDistributionProfileFilter();
			
		if (!$pager)
		    $pager = new VidiunFilterPager();
        
		 //Change the pageSize to support clients who hae had all their dist. profiles listed in Eagle
		$pager->pageSize = 100;
		
		$c = new Criteria();
		$distributionProfileFilter = new DistributionProfileFilter();
		$filter->toObject($distributionProfileFilter);
		
		$distributionProfileFilter->attachToCriteria($c);
		$count = DistributionProfilePeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$list = DistributionProfilePeer::doSelect($c);
		
		$response = new VidiunDistributionProfileListResponse();
		$response->objects = VidiunDistributionProfileArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
	
		return $response;
	}	
	
	/**
	 * @action listByPartner
	 * @param VidiunPartnerFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunDistributionProfileListResponse
	 */
	public function listByPartnerAction(VidiunPartnerFilter $filter = null, VidiunFilterPager $pager = null)
	{
		$c = new Criteria();
		
		if (!is_null($filter))
		{
			
			$partnerFilter = new partnerFilter();
			$filter->toObject($partnerFilter);
			$partnerFilter->set('_gt_id', 0);
			
			$partnerCriteria = new Criteria();
			$partnerFilter->attachToCriteria($partnerCriteria);
			$partnerCriteria->setLimit(1000);
			$partnerCriteria->clearSelectColumns();
			$partnerCriteria->addSelectColumn(PartnerPeer::ID);
			$stmt = PartnerPeer::doSelectStmt($partnerCriteria);
			
			if($stmt->rowCount() < 1000) // otherwise, it's probably all partners
			{
				$partnerIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
				$c->add(DistributionProfilePeer::PARTNER_ID, $partnerIds, Criteria::IN);
			}
		}
			
		if (is_null($pager))
			$pager = new VidiunFilterPager();
			
		$c->addDescendingOrderByColumn(DistributionProfilePeer::CREATED_AT);
		
		$totalCount = DistributionProfilePeer::doCount($c);
		$pager->attachToCriteria($c);
		$list = DistributionProfilePeer::doSelect($c);
		$newList = VidiunDistributionProfileArray::fromDbArray($list, $this->getResponseProfile());
		
		$response = new VidiunDistributionProfileListResponse();
		$response->totalCount = $totalCount;
		$response->objects = $newList;
		return $response;
	}
}

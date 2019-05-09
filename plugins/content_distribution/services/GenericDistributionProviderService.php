<?php
/**
 * Generic Distribution Provider service
 *
 * @service genericDistributionProvider
 * @package plugins.contentDistribution
 * @subpackage api.services
 */
class GenericDistributionProviderService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if($this->getPartnerId() != Partner::ADMIN_CONSOLE_PARTNER_ID)
		$this->applyPartnerFilterForClass('GenericDistributionProvider');
		
		if(!ContentDistributionPlugin::isAllowedPartner(vCurrentContext::$master_partner_id))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, ContentDistributionPlugin::PLUGIN_NAME);
	}
	
	/**
	 * Add new Generic Distribution Provider
	 * 
	 * @action add
	 * @param VidiunGenericDistributionProvider $genericDistributionProvider
	 * @return VidiunGenericDistributionProvider
	 */
	function addAction(VidiunGenericDistributionProvider $genericDistributionProvider)
	{
		$genericDistributionProvider->validatePropertyMinLength("name", 1);
		
		$dbGenericDistributionProvider = new GenericDistributionProvider();
		$genericDistributionProvider->toInsertableObject($dbGenericDistributionProvider);
		$dbGenericDistributionProvider->setPartnerId($this->impersonatedPartnerId);			
		$dbGenericDistributionProvider->setStatus(GenericDistributionProviderStatus::ACTIVE);
		$dbGenericDistributionProvider->save();
		
		$genericDistributionProvider = new VidiunGenericDistributionProvider();
		$genericDistributionProvider->fromObject($dbGenericDistributionProvider, $this->getResponseProfile());
		return $genericDistributionProvider;
	}
	
	/**
	 * Get Generic Distribution Provider by id
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunGenericDistributionProvider
	 * @throws ContentDistributionErrors::GENERIC_DISTRIBUTION_PROVIDER_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbGenericDistributionProvider = GenericDistributionProviderPeer::retrieveByPK($id);
		if (!$dbGenericDistributionProvider)
			throw new VidiunAPIException(ContentDistributionErrors::GENERIC_DISTRIBUTION_PROVIDER_NOT_FOUND, $id);
			
		$genericDistributionProvider = new VidiunGenericDistributionProvider();
		$genericDistributionProvider->fromObject($dbGenericDistributionProvider, $this->getResponseProfile());
		return $genericDistributionProvider;
	}
	
	/**
	 * Update Generic Distribution Provider by id
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunGenericDistributionProvider $genericDistributionProvider
	 * @return VidiunGenericDistributionProvider
	 * @throws ContentDistributionErrors::GENERIC_DISTRIBUTION_PROVIDER_NOT_FOUND
	 */
	function updateAction($id, VidiunGenericDistributionProvider $genericDistributionProvider)
	{
		$dbGenericDistributionProvider = GenericDistributionProviderPeer::retrieveByPK($id);
		if (!$dbGenericDistributionProvider)
			throw new VidiunAPIException(ContentDistributionErrors::GENERIC_DISTRIBUTION_PROVIDER_NOT_FOUND, $id);
		
		if ($genericDistributionProvider->name !== null)
			$genericDistributionProvider->validatePropertyMinLength("name", 1);
			
		$genericDistributionProvider->toUpdatableObject($dbGenericDistributionProvider);
		$dbGenericDistributionProvider->save();
		
		$genericDistributionProvider = new VidiunGenericDistributionProvider();
		$genericDistributionProvider->fromObject($dbGenericDistributionProvider, $this->getResponseProfile());
		return $genericDistributionProvider;
	}
	
	/**
	 * Delete Generic Distribution Provider by id
	 * 
	 * @action delete
	 * @param int $id
	 * @throws ContentDistributionErrors::GENERIC_DISTRIBUTION_PROVIDER_NOT_FOUND
	 * @throws ContentDistributionErrors::CANNOT_DELETE_DEFAULT_DISTRIBUTION_PROVIDER
	 */
	function deleteAction($id)
	{
		$dbGenericDistributionProvider = GenericDistributionProviderPeer::retrieveByPK($id);
		if (!$dbGenericDistributionProvider)
			throw new VidiunAPIException(ContentDistributionErrors::GENERIC_DISTRIBUTION_PROVIDER_NOT_FOUND, $id);

		if($this->getPartnerId() != Partner::ADMIN_CONSOLE_PARTNER_ID && $dbGenericDistributionProvider->getIsDefault())
			throw new VidiunAPIException(ContentDistributionErrors::CANNOT_DELETE_DEFAULT_DISTRIBUTION_PROVIDER);
			
		$dbGenericDistributionProvider->setStatus(GenericDistributionProviderStatus::DELETED);
		$dbGenericDistributionProvider->save();
	}
	
	
	/**
	 * List all distribution providers
	 * 
	 * @action list
	 * @param VidiunGenericDistributionProviderFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunGenericDistributionProviderListResponse
	 */
	function listAction(VidiunGenericDistributionProviderFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunGenericDistributionProviderFilter();
			
		$c = new Criteria();
		$genericDistributionProviderFilter = new GenericDistributionProviderFilter();
		$filter->toObject($genericDistributionProviderFilter);
		
		$genericDistributionProviderFilter->attachToCriteria($c);
		$count = GenericDistributionProviderPeer::doCount($c);
		
		if (! $pager)
			$pager = new VidiunFilterPager ();
		$pager->attachToCriteria($c);
		$list = GenericDistributionProviderPeer::doSelect($c);
		
		$response = new VidiunGenericDistributionProviderListResponse();
		$response->objects = VidiunGenericDistributionProviderArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
	
		return $response;
	}	
}

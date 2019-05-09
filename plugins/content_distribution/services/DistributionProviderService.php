<?php
/**
 * Distribution Provider service
 *
 * @service distributionProvider
 * @package plugins.contentDistribution
 * @subpackage api.services
 */
class DistributionProviderService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('GenericDistributionProvider');
		
		if(!ContentDistributionPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, ContentDistributionPlugin::PLUGIN_NAME);
	}
	
	
	/**
	 * List all distribution providers
	 * 
	 * @action list
	 * @param VidiunDistributionProviderFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunDistributionProviderListResponse
	 */
	function listAction(VidiunDistributionProviderFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunDistributionProviderFilter();
			
		$c = new Criteria();
		if($filter instanceof VidiunGenericDistributionProviderFilter)
		{
			$genericDistributionProviderFilter = new GenericDistributionProviderFilter();
			$filter->toObject($genericDistributionProviderFilter);
			
			$genericDistributionProviderFilter->attachToCriteria($c);
		}
		$count = GenericDistributionProviderPeer::doCount($c);
		
		if (! $pager)
			$pager = new VidiunFilterPager ();
		$pager->attachToCriteria($c);
		$list = GenericDistributionProviderPeer::doSelect($c);
		
		$response = new VidiunDistributionProviderListResponse();
		$response->objects = VidiunDistributionProviderArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
	
		$syndicationProvider = new VidiunSyndicationDistributionProvider();
		$syndicationProvider->fromObject(SyndicationDistributionProvider::get());
		$response->objects[] = $syndicationProvider;
		$response->totalCount++;
		
		$pluginInstances = VidiunPluginManager::getPluginInstances('IVidiunContentDistributionProvider');
		foreach($pluginInstances as $pluginInstance)
		{
			$provider = $pluginInstance->getVidiunProvider();
			if($provider)
			{
				$response->objects[] = $provider;
				$response->totalCount++;
			}
		}
		
		return $response;
	}	
}

<?php

/**
 * Sending beacons on objects
 *
 * @service beacon
 * @package plugins.beacon
 * @subpackage api.services
 */
class BeaconService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		if (($actionName == 'getLast' || $actionName == 'enhanceSearch') && !vCurrentContext::$is_admin_session)
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName . '->' . $this->actionName);
		
		parent::initService($serviceId, $serviceName, $actionName);
	}
	
	/**
	 * @action add
	 * @param VidiunBeacon $beacon
	 * @param VidiunNullableBoolean $shouldLog
	 * @return bool
	 */
	public function addAction(VidiunBeacon $beacon, $shouldLog = VidiunNullableBoolean::FALSE_VALUE)
	{
		$beaconObj = $beacon->toInsertableObject();
		$beaconObj->index($shouldLog);
		
		return true;
	}
	
	/**
	 * @action list
	 * @param VidiunBeaconFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunBeaconListResponse
	 * @throws VidiunAPIException
	 */
	public function listAction(VidiunBeaconFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunBeaconFilter();
		
		if (!$pager)
			$pager = new VidiunFilterPager();
		
		return $filter->getListResponse($pager);
	}
	
	/**
	 * @action enhanceSearch
	 * @param VidiunBeaconEnhanceFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunBeaconListResponse
	 * @throws VidiunAPIException
	 */
	
	public function enhanceSearchAction(VidiunBeaconEnhanceFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunBeaconEnhanceFilter();
		
		if(!$pager)
			$pager = new VidiunFilterPager();
		
		return $filter->enhanceSearch($pager);
	}

	/**
	 * @action searchScheduledResource
	 * @param VidiunBeaconSearchParams $searchParams
	 * @param VidiunPager $pager
	 * @return VidiunBeaconListResponse
	 * @throws VidiunAPIException
	 */

	public function searchScheduledResourceAction(VidiunBeaconSearchParams $searchParams, VidiunPager $pager = null)
	{
		$scheduledResourceSearch = new vScheduledResourceSearch();
		$searchMgr = new vBeaconSearchQueryManger();
		$elasticResponse = $this->initAndSearch($scheduledResourceSearch, $searchParams, $pager);
		$totalCount = $searchMgr->getTotalCount($elasticResponse);
		$responseArray = $searchMgr->getHitsFromElasticResponse($elasticResponse);
		$response = new VidiunBeaconListResponse();
		$response->objects = VidiunBeaconArray::fromDbArray($responseArray);
		$response->totalCount = $totalCount;
		return $response;
	}

	/**
	 * @param vBaseSearch $coreSearchObject
	 * @param $searchParams
	 * @param $pager
	 * @return array
	 */
	private function initAndSearch($coreSearchObject, $searchParams, $pager)
	{
		list($coreParams, $vPager) = self::initSearchActionParams($searchParams, $pager);
		$elasticResults = $coreSearchObject->doSearch($coreParams->getSearchOperator(), $vPager, array(), $coreParams->getObjectId(), $coreParams->getOrderBy());
		return $elasticResults;
	}

	protected static function initSearchActionParams($searchParams, VidiunPager $pager = null)
	{
		$coreParams = $searchParams->toObject();
		$vPager = null;
		if ($pager)
		{
			$vPager = $pager->toObject();
		}

		return array($coreParams, $vPager);
	}

}
<?php

/**
 * @package plugins.beacon
 * @subpackage api.filters
 */
class VidiunBeaconFilter extends VidiunBeaconBaseFilter
{
	/**
	 * @var VidiunBeaconIndexType
	 */
	public $indexTypeEqual;
	
	public function getCoreFilter()
	{
		return null;
	}
	
	public function getListResponse(VidiunFilterPager $pager)
	{
		$searchObject = $this->createSearchObject();
		$searchMgr = new vBeaconSearchQueryManger();
		
		$relatedObjectType = $this->relatedObjectTypeEqual;
		if(!$relatedObjectType)
		{
			$relatedObjectTypes = explode(",", $this->relatedObjectTypeIn);
			$relatedObjectType = $relatedObjectTypes[0];
		}
		
		$indexName = vBeacon::ELASTIC_BEACONS_INDEX_NAME;
		$indexType = null;
		if($relatedObjectType && $relatedObjectType != "") 
		{
			$indexName = vBeacon::$searchIndexNameByBeaconObjectType[$relatedObjectType];
			$indexType = vBeacon::$indexTypeByBeaconObjectType[$relatedObjectType];
		}
		
		$searchQuery = $searchMgr->buildSearchQuery($indexName, $indexType, $searchObject, $pager->pageSize, $pager->calcOffset());
		$elasticQueryResponse = $searchMgr->search($searchQuery);
		$responseArray = $searchMgr->getHitsFromElasticResponse($elasticQueryResponse);
		$totalCount = $searchMgr->getTotalCount($elasticQueryResponse);
		
		$response = new VidiunBeaconListResponse();
		$response->objects = VidiunBeaconArray::fromDbArray($responseArray);
		$response->totalCount = $totalCount;
		return $response;
	}
	
	protected function createSearchObject()
	{
		$searchObject = array();
		
		$searchObject[vESearchQueryManager::TERMS_KEY] = $this->getSearchTerms();
		$searchObject[vESearchQueryManager::RANGE_KEY] = $this->getSearchRangeTerms();
		$searchObject[vESearchQueryManager::ORDER_KEY] = $this->getOrderByObject();
		
		return $searchObject;
	}
	
	private function getSearchTerms()
	{
		$terms = array();
		
		$terms[vBeacon::FIELD_OBJECT_ID] = elasticSearchUtils::formatSearchTerm($this->objectIdIn);
		$terms[vBeacon::FIELD_EVENT_TYPE] = elasticSearchUtils::formatSearchTerm($this->eventTypeIn);
		$terms[vBeacon::FIELD_PARTNER_ID] = vCurrentContext::getCurrentPartnerId();
		
		if(isset($this->indexTypeEqual))
			$terms[vBeacon::FIELD_IS_LOG] = ($this->indexTypeEqual == VidiunBeaconIndexType::LOG) ? "true" : "false";
		
		return $terms;
	}
	
	private function getSearchRangeTerms()
	{
		$range = array();
		
		$range[vBeacon::FIELD_UPDATED_AT][vESearchQueryManager::GTE_KEY] = $this->updatedAtGreaterThanOrEqual;
		$range[vBeacon::FIELD_UPDATED_AT][vESearchQueryManager::LTE_KEY] = $this->updatedAtLessThanOrEqual;
		
		return $range;
	}
	
	private function getOrderByObject()
	{
		if (!$this->orderBy)
			return array();
		
		$orderObject = array();
		$orderByMap = $this->getOrderByMap();
		
		$order_arr = explode(",", $this->orderBy);
		foreach ($order_arr as $order) 
		{
			if (!$order || !isset($orderByMap[$order]))
				continue;
			
			$order = $orderByMap[$order];
			list ($field_name, $ascending) = baseObjectFilter::getFieldAndDirection($order);
			$orderObject[$field_name] = $ascending;
		}
		
		return $orderObject;
	}
}

<?php

/**
 * @package plugins.beacon
 * @subpackage api.filters
 */
class VidiunBeaconEnhanceFilter extends VidiunFilter
{
	/**
	 * @var string
	 */
	public $externalElasticQueryObject;
	
	/**
	 * @var VidiunBeaconIndexType
	 */
	public $indexTypeEqual;
	
	public function getCoreFilter()
	{
		return null;
	}
	
	public function enhanceSearch(VidiunFilterPager $pager)
	{
		$utf8Query = utf8_encode($this->externalElasticQueryObject);
		$queryJsonObject = json_decode($utf8Query, true);
		
		if(!$queryJsonObject)
			throw new VidiunAPIException(APIErrors::INTERNAL_SERVERL_ERROR);
		
		if(!isset($queryJsonObject['query']))
			throw new VidiunAPIException(APIErrors::INTERNAL_SERVERL_ERROR);
		
		$searchQuery = array();
		$searchQuery['body']['query']['bool']['must'] = $queryJsonObject['query'];
		$searchQuery['body']['query']['bool']['filter'][]['term'] = array ("partner_id" => vCurrentContext::getCurrentPartnerId());
		
		if($this->indexTypeEqual)
			$searchQuery['body']['query']['bool']['filter'][]['term'] = array (vBeacon::FIELD_IS_LOG => ($this->indexTypeEqual == VidiunBeaconIndexType::LOG) ? true : false);
		
		$searchQuery[elasticClient::ELASTIC_INDEX_KEY] = vBeacon::ELASTIC_BEACONS_INDEX_NAME;
		$searchQuery[vESearchQueryManager::BODY_KEY][elasticClient::ELASTIC_SIZE_KEY] = $pager->pageSize;
		$searchQuery[vESearchQueryManager::BODY_KEY][elasticClient::ELASTIC_FROM_KEY] = $pager->calcOffset();
		
		if(isset($queryJsonObject['sort']))
			$searchQuery[vESearchQueryManager::BODY_KEY][vESearchQueryManager::SORT_KEY] = $queryJsonObject['sort'];
		
		$searchMgr = new vBeaconSearchQueryManger();
		$responseArray = $searchMgr->search($searchQuery);
		$totalCount = $searchMgr->getTotalCount($responseArray);
		$responseArray = $searchMgr->getHitsFromElasticResponse($responseArray);
		
		$response = new VidiunBeaconListResponse();
		$response->objects = VidiunBeaconArray::fromDbArray($responseArray);
		$response->totalCount = $totalCount;
		return $response;
	}
}

<?php

/**
 * @package plugins.beacon
 * @subpackage model
 */
class vBeaconSearchQueryManger
{
	private static $elasticClient = null;
	
	public function __construct()
	{
		if (self::$elasticClient)
			return self::$elasticClient;
		
		$beaconElasticConfig = vConf::get('beacon', 'elastic');
		$host = isset($beaconElasticConfig['elasticHost']) ? $beaconElasticConfig['elasticHost'] : null;
		$port = isset($beaconElasticConfig['elasticPort']) ? $beaconElasticConfig['elasticPort'] : null;
		
		self::$elasticClient = new elasticClient($host, $port);
	}
	
	public function search($searchQuery)
	{
		return self::$elasticClient->search($searchQuery);
	}
	
	public function get($searchQuery)
	{
		return self::$elasticClient->get($searchQuery);
	}
	
	public function delete($deleteQuery)
	{
		return self::$elasticClient->deleteByQuery($deleteQuery);
	}
	
	public function deleteByObjectId($objectId)
	{
		$deleteObject = array();
		$deleteObject['terms'] = array(vBeacon::FIELD_OBJECT_ID => $objectId, vBeacon::FIELD_PARTNER_ID => vCurrentContext::getCurrentPartnerId());
		
		$deleteQuery = $this->buildSearchQuery(vBeacon::ELASTIC_BEACONS_INDEX_NAME, null, $deleteObject);
		return $this->delete($deleteQuery);
	}
	
	public function buildSearchQuery($indexName, $indexType = null, $searchObject, $pageSize = 30, $pageIndex = 1)
	{
		$query = array();
		
		foreach ($searchObject[vESearchQueryManager::TERMS_KEY] as $key => $value)
		{
			if (!isset($value) || $value === '')
				continue;
			
			$terms = array($key => explode(",",$value));
			$query[] = array(vESearchQueryManager::TERMS_KEY => $terms);
		}
		
		foreach ($searchObject[vESearchQueryManager::RANGE_KEY] as $key => $value)
		{
			if (!$value[vESearchQueryManager::GTE_KEY] && !$value[vESearchQueryManager::LTE_KEY])
				continue;
			
			$range = array();
			
			if($value[vESearchQueryManager::GTE_KEY])
				$range[vESearchQueryManager::GTE_KEY] = $value[vESearchQueryManager::GTE_KEY];
			
			if($value[vESearchQueryManager::LTE_KEY])
				$range[vESearchQueryManager::LTE_KEY] = $value[vESearchQueryManager::LTE_KEY];
			
			$term = array($key => $range);
			$query[] = array(vESearchQueryManager::RANGE_KEY => $term);
		}
		
		
		$sort = array();
		foreach ($searchObject[vESearchQueryManager::ORDER_KEY] as $field_name => $ascending)
		{
			if ($ascending)
				$sort[$field_name] = array(vESearchQueryManager::ORDER_KEY => vESearchQueryManager::ORDER_ASC_KEY);
			else
				$sort[$field_name] = array(vESearchQueryManager::ORDER_KEY => vESearchQueryManager::ORDER_DESC_KEY);
		}
		
		$params = array();
		$params[elasticClient::ELASTIC_INDEX_KEY] = $indexName;
		
		if ($indexType)
			$params[elasticClient::ELASTIC_TYPE_KEY] = $indexType;
		
		$params[vESearchQueryManager::BODY_KEY] = array();
		
		if($pageSize)
			$params[vESearchQueryManager::BODY_KEY][elasticClient::ELASTIC_SIZE_KEY] = $pageSize;
		
		if($pageIndex)
			$params[vESearchQueryManager::BODY_KEY][elasticClient::ELASTIC_FROM_KEY] = $pageIndex;
		
		$params[vESearchQueryManager::BODY_KEY][vESearchQueryManager::QUERY_KEY] = array();
		$params[vESearchQueryManager::BODY_KEY][vESearchQueryManager::QUERY_KEY][vESearchQueryManager::BOOL_KEY] = array();
		$params[vESearchQueryManager::BODY_KEY][vESearchQueryManager::QUERY_KEY][vESearchQueryManager::BOOL_KEY][vESearchQueryManager::FILTER_KEY] = array();
		$params[vESearchQueryManager::BODY_KEY][vESearchQueryManager::QUERY_KEY][vESearchQueryManager::BOOL_KEY][vESearchQueryManager::FILTER_KEY][vESearchQueryManager::BOOL_KEY] = array();
		$params[vESearchQueryManager::BODY_KEY][vESearchQueryManager::QUERY_KEY][vESearchQueryManager::BOOL_KEY][vESearchQueryManager::FILTER_KEY][vESearchQueryManager::BOOL_KEY][vESearchQueryManager::MUST_KEY] = $query;
		
		if (count($sort))
		{
			$params[vESearchQueryManager::BODY_KEY][vESearchQueryManager::SORT_KEY] = $sort;
		}
		
		VidiunLog::debug("Body = " . print_r($params, true));
		
		return $params;
	}
	
	public function getHitsFromElasticResponse($elasticResponse)
	{
		$ret = array();
		
		if (!isset($elasticResponse[vESearchCoreAdapter::HITS_KEY]))
			return $ret;
		
		if (!isset($elasticResponse[vESearchCoreAdapter::HITS_KEY][vESearchCoreAdapter::HITS_KEY]))
			return $ret;
		
		foreach ($elasticResponse[vESearchCoreAdapter::HITS_KEY][vESearchCoreAdapter::HITS_KEY] as $hit)
		{
			$hit['_source']['id'] = $hit['_id'];
			$hit['_source']['indexType'] = $hit['_type'];
			$ret[] = $hit['_source'];
		}
		
		return $ret;
	}
	
	public function getTotalCount($elasticResponse)
	{
		$totalCount = 0;
		
		if (!isset($elasticResponse['hits']))
			return $totalCount;
		
		if (!isset($elasticResponse['hits']['total']))
			return $totalCount;
		
		return $elasticResponse['hits']['total'];
	}
}

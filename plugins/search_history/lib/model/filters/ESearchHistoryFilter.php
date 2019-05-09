<?php
/**
 * @package plugins.searchHistory
 * @subpackage api.filters
 */
class ESearchHistoryFilter extends ESearchBaseFilter
{

	const DEFAULT_SUGGEST_SIZE = 5;
	const STARTS_WITH_PAGE_SIZE = 100;
	const DEFAULT_LIST_SIZE = 500;
	const MAX_SEARCH_TERM_LENGTH = 64;
	const SUGGEST_SEARCH_HISTORY = 'suggest_search_history';

	/**
	 * @var string
	 */
	protected $searchTermStartsWith;

	/**
	 * @var string
	 */
	protected $searchedObjectIn;

	/**
	 * @return string
	 */
	public function getSearchTermStartsWith()
	{
		return $this->searchTermStartsWith;
	}

	/**
	 * @param string $searchTermStartsWith
	 */
	public function setSearchTermStartsWith($searchTermStartsWith)
	{
		$this->searchTermStartsWith = $searchTermStartsWith;
	}

	/**
	 * @return string
	 */
	public function getSearchedObjectIn()
	{
		return $this->searchedObjectIn;
	}

	/**
	 * @param string $searchedObjectIn
	 */
	public function setSearchedObjectIn($searchedObjectIn)
	{
		$this->searchedObjectIn = $searchedObjectIn;
	}

	public function execQueryFromFilter()
	{
		$this->applyFilter();
		$historyClient = new vESearchHistoryElasticClient();
		$elasticResults = $historyClient->searchRecentForUser($this->query);
		return vESearchHistoryCoreAdapter::getCoreESearchHistoryFromResults($elasticResults);
	}

	protected function applyFilter()
	{
		$searchHistoryConfig = vConf::get('search_history', 'elastic', array());
		$partnerId = vCurrentContext::getCurrentPartnerId();
		$vuserId = vCurrentContext::getCurrentVsVuserId();
		if (!$vuserId)
		{
			throw new vESearchHistoryException('Invalid userId', vESearchHistoryException::INVALID_USER_ID);
		}
		$pageSize = isset($searchHistoryConfig['emptyTermListSize']) ? $searchHistoryConfig['emptyTermListSize'] : self::DEFAULT_LIST_SIZE;

		$boolQuery = new vESearchBoolQuery();
		$pidUidContext = searchHistoryUtils::formatPartnerIdUserIdContext($partnerId, $vuserId, searchHistoryUtils::getSearchContext());
		$pidUidContextQuery = new vESearchTermQuery(ESearchHistoryFieldName::PID_UID_CONTEXT, $pidUidContext);
		$boolQuery->addToFilter($pidUidContextQuery);
		$searchTermStartsWith = $this->getSearchTermStartsWith();
		if ($searchTermStartsWith)
		{
			if (strlen($searchTermStartsWith) > self::MAX_SEARCH_TERM_LENGTH)
			{
				$searchTermStartsWith = mb_strcut($searchTermStartsWith, 0, self::MAX_SEARCH_TERM_LENGTH, "utf-8");
			}
			$searchTermStartsWithQuery = new vESearchPrefixQuery(ESearchHistoryFieldName::SEARCH_TERM, elasticSearchUtils::formatSearchTerm($searchTermStartsWith));
			$boolQuery->addToFilter($searchTermStartsWithQuery);
			$pageSize = isset($searchHistoryConfig['completionListSize']) ? $searchHistoryConfig['completionListSize'] : self::STARTS_WITH_PAGE_SIZE;
		}
		if($this->searchedObjectIn)
		{
			$searchedObjects = $this->getSearchedObjectsArray();
			$searchObjectsQuery = new vESearchTermsQuery(ESearchHistoryFieldName::SEARCHED_OBJECT, $searchedObjects);
			$boolQuery->addToFilter($searchObjectsQuery);
		}

		$this->query[vESearchQueryManager::QUERY_KEY] = $boolQuery->getFinalQuery();
		$this->query[vESearchQueryManager::SORT_KEY] = array(ESearchHistoryFieldName::TIMESTAMP => array(vESearchQueryManager::ORDER_KEY => ESearchSortOrder::ORDER_BY_DESC));
		$this->query[vESearchQueryManager::FROM_KEY] = 0;
		$this->query[vESearchQueryManager::SIZE_KEY] = $pageSize;
	}

	protected function getSearchedObjectsArray()
	{
		$searchedObjects = array();
		$searchedObjectsArr = explode(',', $this->getSearchedObjectIn());

		foreach ($searchedObjectsArr as $searchObject)
		{
			$searchedObjects[] = trim($searchObject);
		}
		return $searchedObjects;
	}

}

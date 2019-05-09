<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */

abstract class vBaseESearch extends vBaseSearch
{
	const GLOBAL_HIGHLIGHT_CONFIG = 'globalMaxNumberOfFragments';

	public abstract function getElasticTypeName();

	public abstract function fetchCoreObjectsByIds($ids);

	protected function execSearch(ESearchOperator $eSearchOperator)
	{
		$subQuery = $eSearchOperator::createSearchQuery($eSearchOperator->getSearchItems(), null, $this->queryAttributes, $eSearchOperator->getOperator());
		$this->handleDisplayInSearch();
		if($this->filterOnlyContext)
		{
			$this->mainBoolQuery->addToFilter($subQuery);
		}
		else
		{
			$this->mainBoolQuery->addToMust($subQuery);
		}
		$this->applyElasticSearchConditions();
		$this->addGlobalHighlights();
		$result = $this->elasticClient->search($this->query, true, true);
		$this->addSearchTermsToSearchHistory();
		return $result;
	}

	protected function initQuery(array $statuses, $objectId, vPager $pager = null, ESearchOrderBy $order = null)
	{
		$partnerId = vBaseElasticEntitlement::$partnerId;
		$this->initQueryAttributes($partnerId, $objectId);
		$this->initBaseFilter($partnerId, $statuses, $objectId);
		$this->initPager($pager);
		$this->initOrderBy($order);
	}

	protected function addGlobalHighlights()
	{
		$this->queryAttributes->getQueryHighlightsAttributes()->setScopeToGlobal();
		$numOfFragments = elasticSearchUtils::getNumOfFragmentsByConfigKey(self::GLOBAL_HIGHLIGHT_CONFIG);
		$highlight = new vESearchHighlightQuery($this->queryAttributes->getQueryHighlightsAttributes()->getFieldsToHighlight(), $numOfFragments);
		$highlight = $highlight->getFinalQuery();
		if($highlight)
		{
			$this->query['body']['highlight'] = $highlight;
		}
	}

	protected function addSearchTermsToSearchHistory()
	{
		$searchTerms = $this->queryAttributes->getSearchHistoryTerms();
		$searchTerms = array_unique($searchTerms);
		$searchTerms = array_values($searchTerms);
		if (!$searchTerms)
		{
			VidiunLog::log("Empty search terms, not adding to search history");
			return;
		}

		$searchHistoryInfo = new ESearchSearchHistoryInfo();
		$searchHistoryInfo->setSearchTerms($searchTerms);
		$searchHistoryInfo->setPartnerId(vBaseElasticEntitlement::$partnerId);
		$searchHistoryInfo->setVUserId(vBaseElasticEntitlement::$vuserId);
		$searchHistoryInfo->setSearchContext(searchHistoryUtils::getSearchContext());
		$searchHistoryInfo->setSearchedObject($this->getElasticTypeName());
		$searchHistoryInfo->setTimestamp(time());
		vEventsManager::raiseEventDeferred(new vESearchSearchHistoryInfoEvent($searchHistoryInfo));
	}

}

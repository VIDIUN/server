<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */

abstract class vBaseSearch
{
	protected $elasticClient;
	protected $query;
	protected $queryAttributes;
	protected $mainBoolQuery;

	protected $filterOnlyContext;
	protected $forceInnerHitsSizeOverride;

	public function __construct()
	{
		$this->elasticClient = new elasticClient();
		$this->queryAttributes = new ESearchQueryAttributes();
		$this->mainBoolQuery = new vESearchBoolQuery();
		$this->filterOnlyContext = false;
		$this->forceInnerHitsSizeOverride = false;
	}

	public abstract function doSearch(ESearchOperator $eSearchOperator, vPager $pager = null, $statuses = array(), $objectId = null, ESearchOrderBy $order = null);

	/**
	 * @return ESearchQueryAttributes
	 */
	public function getQueryAttributes()
	{
		return $this->queryAttributes;
	}

	protected function handleDisplayInSearch()
	{
	}

	protected abstract function execSearch(ESearchOperator $eSearchOperator);

	protected abstract function initQuery(array $statuses, $objectId, vPager $pager = null, ESearchOrderBy $order = null);

	protected function initPager(vPager $pager = null)
	{
		if ($pager)
		{
			$this->query['from'] = $pager->calcOffset();
			$this->query['size'] = $pager->getPageSize();
		}
	}

	protected function initOrderBy(ESearchOrderBy $order = null)
	{
		if($order)
		{
			$sortConditions = $this->getSortConditions($order);
			if(count($sortConditions))
			{
				$sortConditions[] = '_score';
			}

			$this->query['body']['sort'] = $sortConditions;
		}
	}

	protected function getSortConditions(ESearchOrderBy $order)
	{
		$orderItems = $order->getOrderItems();
		$fields = array();
		$sortConditions = array();
		foreach ($orderItems as $orderItem)
		{
			$field = $orderItem->getSortField();
			if(isset($fields[$field]))
			{
				VidiunLog::log("Order by condition already set for field [$field]" );
				continue;
			}

			$fields[$field] = true;
			$conditions = $orderItem->getSortConditions();
			foreach ($conditions as $condition)
			{
				$sortConditions[] = $condition;
			}
		}

		return $sortConditions;
	}

	protected function initBaseFilter($partnerId, array $statuses, $objectId)
	{
		$partnerStatus = array();
		foreach ($statuses as $status)
		{
			$partnerStatus[] = elasticSearchUtils::formatPartnerStatus($partnerId, $status);
		}

		$partnerStatusQuery = new vESearchTermsQuery('partner_status', $partnerStatus);
		$this->mainBoolQuery->addToFilter($partnerStatusQuery);

		if($objectId)
		{
			$id = elasticSearchUtils::formatSearchTerm($objectId);
			$idQuery = new vESearchTermQuery('_id', $id);
			$this->mainBoolQuery->addToFilter($idQuery);
		}

		//return only the object id
		$this->query['body']['_source'] = false;
	}

	protected function applyElasticSearchConditions()
	{
		$this->query['body']['query'] = $this->mainBoolQuery->getFinalQuery();
	}

	protected function initQueryAttributes($partnerId, $objectId)
	{
		$this->initPartnerLanguages($partnerId);
		$this->queryAttributes->setObjectId($objectId);
		$this->initOverrideInnerHits($objectId);
	}

	protected function initPartnerLanguages($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
		{
			return;
		}

		$partnerLanguages = $partner->getESearchLanguages();
		if(!count($partnerLanguages))
		{
			//if no languages are set for partner - set the default to english
			$partnerLanguages = array('english');
		}

		$this->queryAttributes->setPartnerLanguages($partnerLanguages);
	}

	protected function initOverrideInnerHits($objectId)
	{
		if(!$objectId && !$this->forceInnerHitsSizeOverride)
		{
			return;
		}

		$innerHitsConfig = vConf::get('innerHits', 'elastic');
		$overrideInnerHitsSize = isset($innerHitsConfig['innerHitsWithObjectId']) ? $innerHitsConfig['innerHitsWithObjectId'] : null;
		$this->queryAttributes->setOverrideInnerHitsSize($overrideInnerHitsSize);
	}

	public function setFilterOnlyContext()
	{
		$this->filterOnlyContext = true;
	}

	public function setForceInnerHitsSizeOverride()
	{
		$this->forceInnerHitsSizeOverride = true;
	}

}
<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */
class vEntrySearch extends vBaseESearch
{

    protected $isInitialized;
    private $entryEntitlementQuery;
    private $parentEntryEntitlementQuery;

    public function __construct()
    {
        $this->isInitialized = false;
        parent::__construct();
		$this->queryAttributes->setQueryFilterAttributes(new ESearchEntryQueryFilterAttributes());
    }

    protected function handleDisplayInSearch()
    {
        if($this->queryAttributes->getObjectId())
            return;

        $displayInSearchQuery = $this->queryAttributes->getQueryFilterAttributes()->getDisplayInSearchFilter();
        $this->mainBoolQuery->addToFilter($displayInSearchQuery);
    }

    public function doSearch(ESearchOperator $eSearchOperator, vPager $pager = null, $entriesStatus = array(), $objectId = null, ESearchOrderBy $order = null)
    {
        vEntryElasticEntitlement::init();
        if (!count($entriesStatus))
            $entriesStatus = array(entryStatus::READY);
        $this->initQuery($entriesStatus, $objectId, $pager, $order);
        $this->initEntitlement($eSearchOperator, $objectId);
        $result = $this->execSearch($eSearchOperator);
        return $result;
    }

    protected function initQuery(array $statuses, $objectId, vPager $pager = null, ESearchOrderBy $order = null)
    {
        $this->query = array(
            'index' => ElasticIndexMap::ELASTIC_ENTRY_INDEX,
            'type' => ElasticIndexMap::ELASTIC_ENTRY_TYPE
        );
        parent::initQuery($statuses, $objectId, $pager, $order);
    }

    protected function initEntitlement(ESearchOperator $eSearchOperator, $objectId)
    {
        vEntryElasticEntitlement::setFilteredCategoryIds($eSearchOperator, $objectId);
        $contributors = vEntryElasticEntitlement::getEntitlementContributors();
        foreach ($contributors as $contributor)
        {
            if($contributor::shouldContribute())
            {
                if(!$this->isInitialized)
                    $this->initEntryEntitlementQueries();
                $contributor::applyCondition($this->entryEntitlementQuery, $this->parentEntryEntitlementQuery);
            }
        }
    }

    protected function initEntryEntitlementQueries()
    {
        $this->parentEntryEntitlementQuery = null;

        if(vEntryElasticEntitlement::$parentEntitlement)
        {
            $EntitlementQueryBool = new vESearchBoolQuery();

            //Validate that parent entry property exist
            $parentQueryBool = new vESearchBoolQuery();
            $parentExistQuery = new vESearchExistsQuery('parent_id');
            $parentQueryBool->addToFilter($parentExistQuery);
            //assign by reference to create alias
            $this->parentEntryEntitlementQuery = &$parentQueryBool;
            $EntitlementQueryBool->addToShould($parentQueryBool);

            //Validate that parent entry property does not exist
            $entryQueryBool = new vESearchBoolQuery();
            $parentNotExistQuery = new vESearchExistsQuery('parent_id');
            $entryQueryBool->addToMustNot($parentNotExistQuery);
            //assign by reference to create alias
            $this->entryEntitlementQuery = &$entryQueryBool;
            $EntitlementQueryBool->addToShould($entryQueryBool);

            //add to main query filter
            $this->mainBoolQuery->addToFilter($EntitlementQueryBool);
        }
        else
        {
            $EntitlementQueryBool = new vESearchBoolQuery();
            //assign by reference to create alias
            $this->entryEntitlementQuery = &$EntitlementQueryBool;
            //add to main query filter
            $this->mainBoolQuery->addToFilter($EntitlementQueryBool);
        }
        $this->isInitialized = true;
    }

    public function getElasticTypeName()
    {
        return ElasticIndexMap::ELASTIC_ENTRY_TYPE;
    }

    public function fetchCoreObjectsByIds($ids)
    {
        $entries = entryPeer::retrieveByPKsNoFilter($ids);
        entryPeer::fetchPlaysViewsData($entries);
        return $entries;
    }

}

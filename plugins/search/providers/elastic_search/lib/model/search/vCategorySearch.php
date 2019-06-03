<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */

class vCategorySearch extends vBaseESearch
{

    public function __construct()
    {
        parent::__construct();
    }

    public function doSearch(ESearchOperator $eSearchOperator, vPager $pager = null, $statuses = array(), $objectId = null, ESearchOrderBy $order = null)
    {
        vCategoryElasticEntitlement::init();
        if (!count($statuses))
            $statuses = array(CategoryStatus::ACTIVE);

        $this->initQuery($statuses, $objectId, $pager, $order);
        $this->initEntitlement();
        $result = $this->execSearch($eSearchOperator);
        return $result;
    }

    protected function initQuery(array $statuses, $objectId, vPager $pager = null, ESearchOrderBy $order = null)
    {
        $this->query = array(
            'index' => ElasticIndexMap::ELASTIC_CATEGORY_INDEX,
            'type' => ElasticIndexMap::ELASTIC_CATEGORY_TYPE
        );

        parent::initQuery($statuses, $objectId, $pager, $order);
    }

    private function initEntitlement()
	{
		$entitlementFilterQueries = vCategoryElasticEntitlement::getEntitlementFilterQueries();
		if($entitlementFilterQueries)
		{
			$this->mainBoolQuery->addQueriesToFilter($entitlementFilterQueries);
		}
	}

    public function getElasticTypeName()
    {
        return ElasticIndexMap::ELASTIC_CATEGORY_TYPE;
    }

    public function fetchCoreObjectsByIds($ids)
    {
        return categoryPeer::retrieveByPKsNoFilter($ids);
    }

}

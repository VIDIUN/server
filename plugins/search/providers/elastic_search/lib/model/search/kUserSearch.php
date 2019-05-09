<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */

class vUserSearch extends vBaseESearch
{

    public function __construct()
    {
        parent::__construct();
    }
    
    public function doSearch(ESearchOperator $eSearchOperator, vPager $pager = null, $statuses = array(), $objectId = null, ESearchOrderBy $order = null)
    {
        vUserElasticEntitlement::init();
        if (!count($statuses))
            $statuses = array(VuserStatus::ACTIVE);
        $this->initQuery($statuses, $objectId, $pager, $order);
        $result = $this->execSearch($eSearchOperator);
        return $result;
    }

    protected function initQuery(array $statuses, $objectId, vPager $pager = null, ESearchOrderBy $order = null)
    {
        $this->query = array(
            'index' => ElasticIndexMap::ELASTIC_VUSER_INDEX,
            'type' => ElasticIndexMap::ELASTIC_VUSER_TYPE
        );

        parent::initQuery($statuses, $objectId, $pager, $order);
    }

    public function getElasticTypeName()
    {
        return ElasticIndexMap::ELASTIC_VUSER_TYPE;
    }

    public function fetchCoreObjectsByIds($ids)
    {
        return vuserPeer::retrieveByPKs($ids);
    }

}

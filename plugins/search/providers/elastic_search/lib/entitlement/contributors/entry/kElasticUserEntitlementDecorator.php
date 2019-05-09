<?php
/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

class vElasticUserEntitlementDecorator implements IVidiunESearchEntryEntitlementDecorator
{

	public static function shouldContribute()
	{
		if(vEntryElasticEntitlement::$userEntitlement)
			return true;

		return false;
	}

	public static function getEntitlementCondition(array $params = array(), $fieldPrefix = '')
	{
		$conditions = array();

		$userEditPreFetchGroupCondition = new vESearchTermsQuery("{$fieldPrefix}entitled_vusers_edit",
			array('index' => ElasticIndexMap::ELASTIC_VUSER_INDEX,'type' => ElasticIndexMap::ELASTIC_VUSER_TYPE,
				'id' => $params['vuserId'],	'path' => 'group_ids'));
		$conditions[] = $userEditPreFetchGroupCondition;
		$userEditCondition = new vESearchTermQuery("{$fieldPrefix}entitled_vusers_edit",$params['vuserId']);
		$conditions[] = $userEditCondition;

		$userPublishPreFetchGroupCondition = new vESearchTermsQuery("{$fieldPrefix}entitled_vusers_publish",
			array('index' => ElasticIndexMap::ELASTIC_VUSER_INDEX,'type' => ElasticIndexMap::ELASTIC_VUSER_TYPE,
				'id' => $params['vuserId'],	'path' => 'group_ids'));
		$conditions[] = $userPublishPreFetchGroupCondition;
		$userPublishCondition = new vESearchTermQuery("{$fieldPrefix}entitled_vusers_publish",$params['vuserId']);
		$conditions[] = $userPublishCondition;

		$userViewPreFetchGroupCondition = new vESearchTermsQuery("{$fieldPrefix}entitled_vusers_view",
			array('index' => ElasticIndexMap::ELASTIC_VUSER_INDEX,'type' => ElasticIndexMap::ELASTIC_VUSER_TYPE,
				'id' => $params['vuserId'],	'path' => 'group_ids'));
		$conditions[] = $userViewPreFetchGroupCondition;
		$userViewCondition = new vESearchTermQuery("{$fieldPrefix}entitled_vusers_view",$params['vuserId']);
		$conditions[] = $userViewCondition;

		$userPreFetchGroupCondition = new vESearchTermsQuery("{$fieldPrefix}vuser_id",
			array('index' => ElasticIndexMap::ELASTIC_VUSER_INDEX,'type' => ElasticIndexMap::ELASTIC_VUSER_TYPE,
				'id' => $params['vuserId'],	'path' => 'group_ids'));
		$conditions[] = $userPreFetchGroupCondition;
		$userCondition = new vESearchTermQuery("{$fieldPrefix}vuser_id",$params['vuserId']);
		$conditions[] = $userCondition;
		return $conditions;
	}

	public static function applyCondition(&$entryQuery, &$parentEntryQuery)
	{
		$vuserId = vEntryElasticEntitlement::$vuserId;
		if(!$vuserId)
		{
			VidiunLog::log('cannot add user entitlement to elastic without a vuserId - setting vuser id to -1');
			$vuserId = -1;
		}
		$params['vuserId'] = $vuserId;

		if($parentEntryQuery)
		{
			//add parent conditions
			$conditions = self::getEntitlementCondition($params, 'parent_entry.');
			foreach ($conditions as $condition)
			{
				$parentEntryQuery->addToShould($condition);
			}
		}
		$conditions = self::getEntitlementCondition($params);
		foreach ($conditions as $condition)
		{
			$entryQuery->addToShould($condition);
		}
	}
}

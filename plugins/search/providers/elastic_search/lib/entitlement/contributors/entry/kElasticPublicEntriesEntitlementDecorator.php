<?php
/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

class vElasticPublicEntriesEntitlementDecorator implements IVidiunESearchEntryEntitlementDecorator
{

	public static function shouldContribute()
	{
		if(vEntryElasticEntitlement::$publicEntries || vEntryElasticEntitlement::$publicActiveEntries)
			return true;
		
		return false;
	}

	public static function getEntitlementCondition(array $params = array(), $fieldPrefix = '')
	{
		$condition = new vESearchBoolQuery();
		$statuses = $params['category_statues'];
		$statuses = array_map('elasticSearchUtils::formatCategoryEntryStatus', $statuses);
		$termsQuery = new vESearchTermsQuery($fieldPrefix . ESearchBaseCategoryEntryItem::CATEGORY_IDS_MAPPING_FIELD, $statuses);
		$condition->addToMustNot($termsQuery);
		return $condition;
	}

	public static function applyCondition(&$entryQuery, &$parentEntryQuery)
	{

		if(vEntryElasticEntitlement::$publicEntries)
			$params['category_statues'] = array(CategoryEntryStatus::ACTIVE, CategoryEntryStatus::PENDING);
		else if(vEntryElasticEntitlement::$publicActiveEntries)
			$params['category_statues'] = array(CategoryEntryStatus::ACTIVE);

		if($parentEntryQuery)
		{
			$condition = self::getEntitlementCondition($params, 'parent_entry.');
			$parentEntryQuery->addToShould($condition);
		}
		$condition = self::getEntitlementCondition($params);
		$entryQuery->addToShould($condition);
	}
}

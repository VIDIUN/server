<?php
/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

class vElasticEntryDisableEntitlementDecorator implements IVidiunESearchEntryEntitlementDecorator
{
	public static function shouldContribute()
	{
		if(vEntryElasticEntitlement::$entriesDisabledEntitlement && count(vEntryElasticEntitlement::$entriesDisabledEntitlement))
			return true;

		return false;
	}
	
	public static function getEntitlementCondition(array $params = array(), $fieldPrefix = '')
	{
		$conditions = new vESearchTermsQuery("{$fieldPrefix}_id", $params['entryIds']);
		return $conditions;
	}
	
	public static function applyCondition(&$entryQuery, &$parentEntryQuery)
	{
		$params['entryIds'] = vEntryElasticEntitlement::$entriesDisabledEntitlement;
		if($parentEntryQuery)
		{
			$conditions = self::getEntitlementCondition($params, 'parent_entry.entry');
			$parentEntryQuery->addToShould($conditions);
		}
		$conditions = self::getEntitlementCondition($params);
		$entryQuery->addToShould($conditions);
	}
}

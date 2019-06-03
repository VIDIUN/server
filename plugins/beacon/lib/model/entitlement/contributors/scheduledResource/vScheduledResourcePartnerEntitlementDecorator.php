<?php
/**
 * @package plugins.beacon
 * @subpackage model.entitlement
 */
class vScheduledResourcePartnerEntitlementDecorator extends vScheduledResourceSearchEntitlementDecorator
{
	public static function getEntitlementCondition(array $params = array(), $fieldPrefix = '')
	{
		return new vESearchTermQuery(BeaconScheduledResourceFieldName::PARTNER_ID, vBaseElasticEntitlement::$partnerId);
	}
}
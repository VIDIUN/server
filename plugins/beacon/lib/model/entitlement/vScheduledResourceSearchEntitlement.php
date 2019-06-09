<?php
/**
 * @package plugins.beacon
 * @subpackage model.entitlement
 */

class vScheduledResourceSearchEntitlement extends vBaseElasticEntitlement
{
	protected static $entitlementContributors = array(
		'vScheduledResourcePartnerEntitlementDecorator'
	);

	protected static function initialize()
	{
		self::$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
	}
}

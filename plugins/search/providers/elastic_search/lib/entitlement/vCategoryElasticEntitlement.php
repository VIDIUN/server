<?php
/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

class vCategoryElasticEntitlement extends vBaseElasticEntitlement
{
	protected static $entitlementContributors = array(
		'vElasticDisplayAndMemberEntitlementDecorator',
		'vElasticPrivacyContextEntitlementDecorator',
	);
}

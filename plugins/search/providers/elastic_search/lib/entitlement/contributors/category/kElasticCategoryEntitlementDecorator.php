<?php
/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

abstract class vElasticCategoryEntitlementDecorator implements IVidiunESearchEntitlementDecorator
{
	public static function shouldContribute()
	{
		return vEntitlementUtils::getEntitlementEnforcement();
	}
}

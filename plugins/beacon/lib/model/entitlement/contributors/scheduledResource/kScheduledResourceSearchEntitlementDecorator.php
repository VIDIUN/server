<?php
/**
 * @package plugins.beacon
 * @subpackage model.entitlement
 */
abstract class vScheduledResourceSearchEntitlementDecorator implements IVidiunESearchEntitlementDecorator
{
	public static function shouldContribute()
	{
		return true;
	}
}
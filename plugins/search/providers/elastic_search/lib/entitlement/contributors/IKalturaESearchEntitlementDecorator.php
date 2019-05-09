<?php

/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

interface IVidiunESearchEntitlementDecorator
{
	public static function shouldContribute();
	public static function getEntitlementCondition(array $params = array(), $fieldPrefix ='');
}

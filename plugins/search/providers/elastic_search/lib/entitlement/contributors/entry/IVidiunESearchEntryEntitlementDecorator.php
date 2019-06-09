<?php

/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

interface IVidiunESearchEntryEntitlementDecorator extends IVidiunESearchEntitlementDecorator
{
	public static function applyCondition(&$entryQuery, &$parentEntryQuery);
}

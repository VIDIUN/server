<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunRuleActionType extends VidiunDynamicEnum implements RuleActionType
{
	public static function getEnumClass()
	{
		return 'RuleActionType';
	}
}
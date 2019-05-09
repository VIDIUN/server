<?php
/**
 * @package api
 * @subpackage filters.enum
 */
class VidiunSearchConditionComparison extends VidiunDynamicEnum implements searchConditionComparison
{
	public static function getEnumClass()
	{
		return 'searchConditionComparison';
	}
}
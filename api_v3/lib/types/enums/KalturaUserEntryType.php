<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunUserEntryType extends VidiunDynamicEnum implements UserEntryType
{
	public static function getEnumClass()
	{
		return 'UserEntryType';
	}
}
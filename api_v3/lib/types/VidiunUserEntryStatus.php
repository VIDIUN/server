<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunUserEntryStatus extends VidiunDynamicEnum implements UserEntryStatus
{
	public static function getEnumClass()
	{
		return 'UserEntryStatus';
	}
}


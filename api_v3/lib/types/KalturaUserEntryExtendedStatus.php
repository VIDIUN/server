<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunUserEntryExtendedStatus extends VidiunDynamicEnum implements UserEntryExtendedStatus
{
	public static function getEnumClass()
	{
		return 'UserEntryExtendedStatus';
	}
}
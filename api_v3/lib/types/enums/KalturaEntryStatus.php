<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunEntryStatus extends VidiunDynamicEnum implements entryStatus
{
	public static function getEnumClass()
	{
		return 'entryStatus';
	}
}
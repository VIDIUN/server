<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunEntryReplacementStatus extends VidiunDynamicEnum implements entryReplacementStatus
{
	public static function getEnumClass()
	{
		return 'entryReplacementStatus';
	}
}
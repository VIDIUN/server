<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunEntryType extends VidiunDynamicEnum implements entryType
{
	public static function getEnumClass()
	{
		return 'entryType';
	}
}

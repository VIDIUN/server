<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunEntryCapability extends VidiunDynamicEnum implements EntryCapability
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'EntryCapability';
	}
}

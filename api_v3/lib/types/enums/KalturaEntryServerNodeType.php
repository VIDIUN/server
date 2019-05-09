<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunEntryServerNodeType extends VidiunDynamicEnum implements EntryServerNodeType
{
	public static function getEnumClass()
	{
		return 'EntryServerNodeType';
	}
}
<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunSourceType extends VidiunDynamicEnum implements EntrySourceType
{
	public static function getEnumClass()
	{
		return 'EntrySourceType';
	}
}

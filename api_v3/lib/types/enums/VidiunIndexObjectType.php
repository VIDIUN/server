<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunIndexObjectType extends VidiunDynamicEnum implements IndexObjectType
{
	public static function getEnumClass()
	{
		return 'IndexObjectType';
	}
}
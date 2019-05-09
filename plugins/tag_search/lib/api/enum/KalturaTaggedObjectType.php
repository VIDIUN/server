<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunTaggedObjectType extends VidiunDynamicEnum implements taggedObjectType
{
	public static function getEnumClass()
	{
		return 'taggedObjectType';
	}
}
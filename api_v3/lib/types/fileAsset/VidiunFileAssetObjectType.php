<?php
/**
 * @package api
 * @subpackage filters.enum
 */
class VidiunFileAssetObjectType extends VidiunDynamicEnum implements FileAssetObjectType
{
	public static function getEnumClass()
	{
		return 'FileAssetObjectType';
	}
}
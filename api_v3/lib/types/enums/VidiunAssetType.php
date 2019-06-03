<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunAssetType extends VidiunDynamicEnum implements assetType
{
	public static function getEnumClass()
	{
		return 'assetType';
	}
}

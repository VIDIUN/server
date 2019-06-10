<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunFileAssetStatus extends VidiunDynamicEnum implements FileAssetStatus
{
	public static function getEnumClass()
	{
		return 'FileAssetStatus';
	}
}
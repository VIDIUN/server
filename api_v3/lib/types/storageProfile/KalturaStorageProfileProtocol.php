<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunStorageProfileProtocol extends VidiunDynamicEnum implements StorageProfileProtocol
{
	public static function getEnumClass()
	{
		return 'StorageProfileProtocol';
	}
}

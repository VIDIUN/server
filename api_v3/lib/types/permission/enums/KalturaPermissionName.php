<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunPermissionName extends VidiunDynamicEnum implements PermissionName
{
	// see permissionName interface
	
	public static function getEnumClass()
	{
		return 'PermissionName';
	}
}
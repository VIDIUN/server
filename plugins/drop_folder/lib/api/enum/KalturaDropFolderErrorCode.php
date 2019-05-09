<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.enum
 */
class VidiunDropFolderErrorCode extends VidiunDynamicEnum implements DropFolderErrorCode
{
	// see DropFolderErrorCode interface
	
	public static function getEnumClass()
	{
		return 'DropFolderErrorCode';
	}
}

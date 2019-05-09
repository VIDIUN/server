<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.enum
 */
class VidiunDropFolderFileErrorCode extends VidiunDynamicEnum implements DropFolderFileErrorCode
{
	// see DropFolderFileErrorCode interface
	
	public static function getEnumClass()
	{
		return 'DropFolderFileErrorCode';
	}
}

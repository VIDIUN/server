<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.enum
 */
class VidiunDropFolderFileHandlerType extends VidiunDynamicEnum implements DropFolderFileHandlerType
{
	public static function getEnumClass()
	{
		return 'DropFolderFileHandlerType';
	}
}
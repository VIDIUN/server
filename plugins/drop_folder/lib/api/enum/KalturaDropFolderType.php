<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.enum
 */
class VidiunDropFolderType extends VidiunDynamicEnum implements DropFolderType
{
	public static function getEnumClass()
	{
		return 'DropFolderType';
	}
}
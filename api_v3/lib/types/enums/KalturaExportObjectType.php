<?php

/**
 * @package api
 * @subpackage enum
 */
class VidiunExportObjectType extends VidiunDynamicEnum implements ExportObjectType
{
	public static function getEnumClass()
	{
		return 'ExportObjectType';
	}
}
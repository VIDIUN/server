<?php
/**
 * @package plugins.metadata
 * @subpackage api.enum
 */
class VidiunMetadataObjectType extends VidiunDynamicEnum implements MetadataObjectType
{
	public static function getEnumClass()
	{
		return 'MetadataObjectType';
	}
}
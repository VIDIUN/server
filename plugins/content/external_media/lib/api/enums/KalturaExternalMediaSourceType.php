<?php
/**
 * @package plugins.externalMedia
 * @subpackage api.enum
 */
class VidiunExternalMediaSourceType extends VidiunDynamicEnum implements ExternalMediaSourceType
{
	public static function getEnumClass()
	{
		return 'ExternalMediaSourceType';
	}
}

<?php
/**
 * @package api
 * @subpackage api.enum
 */
class VidiunDrmSchemeName extends VidiunDynamicEnum implements DrmSchemeName
{
	public static function getEnumClass()
	{
		return 'DrmSchemeName';
	}
}
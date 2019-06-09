<?php
/**
 * @package plugins.drm
 * @subpackage api.enum
 */
class VidiunDrmLicenseType extends VidiunDynamicEnum implements DrmLicenseType
{
	public static function getEnumClass()
	{
		return 'DrmLicenseType';
	}
}
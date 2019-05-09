<?php
/**
 * @package plugins.drm
 * @subpackage api.enum
 */
class VidiunDrmLicenseScenario extends VidiunDynamicEnum implements DrmLicenseScenario
{
	public static function getEnumClass()
	{
		return 'DrmLicenseScenario';
	}
}
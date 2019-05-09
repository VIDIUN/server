<?php
/**
 * @package plugins.drm
 * @subpackage api.enum
 */
class VidiunDrmProviderType extends VidiunDynamicEnum implements DrmProviderType
{
	public static function getEnumClass()
	{
		return 'DrmProviderType';
	}
}
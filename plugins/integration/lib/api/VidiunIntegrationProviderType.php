<?php
/**
 * @package plugins.integration
 * @subpackage api.enum
 * @see IntegrationProviderType
 */
class VidiunIntegrationProviderType extends VidiunDynamicEnum implements IntegrationProviderType
{
	public static function getEnumClass()
	{
		return 'IntegrationProviderType';
	}

	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
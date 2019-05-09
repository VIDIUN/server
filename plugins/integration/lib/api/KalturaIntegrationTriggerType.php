<?php
/**
 * @package plugins.integration
 * @subpackage api.enum
 * @see IntegrationTriggerType
 */
class VidiunIntegrationTriggerType extends VidiunDynamicEnum implements IntegrationTriggerType
{
	public static function getEnumClass()
	{
		return 'IntegrationTriggerType';
	}

	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
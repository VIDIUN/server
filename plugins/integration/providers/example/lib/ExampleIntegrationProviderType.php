<?php
/**
 * @package plugins.exampleIntegration
 * @subpackage lib.enum
 */
class ExampleIntegrationProviderType implements IVidiunPluginEnum, IntegrationProviderType
{
	const EXAMPLE = 'Example';
	
	public static function getAdditionalValues()
	{
		return array(
			'EXAMPLE' => self::EXAMPLE,
		);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}

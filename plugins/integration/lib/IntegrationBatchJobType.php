<?php
/**
 * @package plugins.integration
 * @subpackage lib.enum
 */
class IntegrationBatchJobType implements IVidiunPluginEnum, BatchJobType
{
	const INTEGRATION = 'Integration';
	
	public static function getAdditionalValues()
	{
		return array(
			'INTEGRATION' => self::INTEGRATION,
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

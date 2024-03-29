<?php
/**
 * @package plugins.huluDistribution
 * @subpackage lib
 */
class HuluDistributionProviderType implements IVidiunPluginEnum, DistributionProviderType
{
	const HULU = 'HULU';
	
	public static function getAdditionalValues()
	{
		return array(
			'HULU' => self::HULU,
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

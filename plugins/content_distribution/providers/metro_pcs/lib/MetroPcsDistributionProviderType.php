<?php
/**
 * @package plugins.metroPcsDistribution
 * @subpackage lib
 */
class MetroPcsDistributionProviderType implements IVidiunPluginEnum, DistributionProviderType
{
	const METRO_PCS = 'METRO_PCS';
	
	public static function getAdditionalValues()
	{
		return array(
			'METRO_PCS' => self::METRO_PCS,
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

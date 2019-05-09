<?php
/**
 * @package plugins.dailymotionDistribution
 * @subpackage lib
 */
class DailymotionDistributionProviderType implements IVidiunPluginEnum, DistributionProviderType
{
	const DAILYMOTION = 'DAILYMOTION';
	
	public static function getAdditionalValues()
	{
		return array(
			'DAILYMOTION' => self::DAILYMOTION,
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

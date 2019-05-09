<?php
/**
 * @package plugins.yahooDistribution
 * @subpackage lib
 */
class YahooDistributionProviderType implements IVidiunPluginEnum, DistributionProviderType
{
	const YAHOO = 'YAHOO';
	
	public static function getAdditionalValues()
	{
		return array(
			'YAHOO' => self::YAHOO,
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

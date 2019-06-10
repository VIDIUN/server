<?php
/**
 * @package plugins.tvinciDistribution
 * @subpackage lib
 */
class TvinciDistributionProviderType implements IVidiunPluginEnum, DistributionProviderType
{
	const TVINCI = 'TVINCI';
	
	public static function getAdditionalValues()
	{
		return array(
			'TVINCI' => self::TVINCI,
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

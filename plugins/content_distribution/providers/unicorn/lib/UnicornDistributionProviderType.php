<?php
/**
 * @package plugins.unicornDistribution
 * @subpackage lib
 */
class UnicornDistributionProviderType implements IVidiunPluginEnum, DistributionProviderType
{
	const UNICORN = 'UNICORN';
	
	public static function getAdditionalValues()
	{
		return array('UNICORN' => self::UNICORN);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}

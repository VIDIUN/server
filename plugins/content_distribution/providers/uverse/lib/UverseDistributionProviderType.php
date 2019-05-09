<?php
/**
 * @package plugins.uverseDistribution
 * @subpackage lib
 */
class UverseDistributionProviderType implements IVidiunPluginEnum, DistributionProviderType
{
	const UVERSE = 'UVERSE';
	
	public static function getAdditionalValues()
	{
		return array(
			'UVERSE' => self::UVERSE,
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

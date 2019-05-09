<?php
/**
 * @package plugins.crossVidiunDistribution
 * @subpackage lib
 */
class CrossVidiunDistributionProviderType implements IVidiunPluginEnum, DistributionProviderType
{
	const CROSS_VIDIUN = 'CROSS_VIDIUN';
	
	public static function getAdditionalValues()
	{
		return array(
			'CROSS_VIDIUN' => self::CROSS_VIDIUN,
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

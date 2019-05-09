<?php
/**
 * @package plugins.msnDistribution
 * @subpackage lib
 */
class MsnDistributionProviderType implements IVidiunPluginEnum, DistributionProviderType
{
	const MSN = 'MSN';
	
	public static function getAdditionalValues()
	{
		return array(
			'MSN' => self::MSN,
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

<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.enum
 */
class VidiunDistributionProviderType extends VidiunDynamicEnum implements DistributionProviderType
{
	public static function getEnumClass()
	{
		return 'DistributionProviderType';
	}
}
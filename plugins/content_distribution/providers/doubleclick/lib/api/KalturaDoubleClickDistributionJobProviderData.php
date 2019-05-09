<?php
/**
* @package plugins.doubleClickDistribution
 * @subpackage api.objects
 */
class VidiunDoubleClickDistributionJobProviderData extends VidiunDistributionJobProviderData
{
	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
	}

	private static $map_between_objects = array();

	public function getMapBetweenObjects()
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}	
}

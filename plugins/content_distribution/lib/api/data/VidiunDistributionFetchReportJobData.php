<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunDistributionFetchReportJobData extends VidiunDistributionJobData
{
	/**
	 * @var int
	 */
	public $plays;
	
	/**
	 * @var int
	 */
	public $views;
	
	
	private static $map_between_objects = array
	(
		"plays" ,
		"views" ,
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}

<?php

/**
 * @package plugins.reach
 * @subpackage api.filters
 */

class VidiunReachReportInputFilter extends VidiunReportInputFilter
{

	private static $map_between_objects = array
	(
		'serviceType',
		'serviceFeature',
		'turnAroundTime',
	);

	/**
	 * @var VidiunVendorServiceType
	 */
	public $serviceType;
	
	/**
	 * @var VidiunVendorServiceFeature
	 */
	public $serviceFeature;
	
	/**
	 * @var VidiunVendorServiceTurnAroundTime
	 */
	public $turnAroundTime;

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toReportsInputFilter($reportInputFilter = null)
	{
		if (!$reportInputFilter)
			$reportInputFilter = new reachReportsInputFilter();

		return parent::toReportsInputFilter($reportInputFilter);
	}
}
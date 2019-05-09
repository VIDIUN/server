<?php
/**
 * @package plugins.exampleDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunExampleDistributionProviderBaseFilter extends VidiunDistributionProviderFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), VidiunExampleDistributionProviderBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), VidiunExampleDistributionProviderBaseFilter::$order_by_map);
	}
}

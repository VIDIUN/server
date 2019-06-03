<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunDistributionProviderBaseFilter extends VidiunFilter
{
	static private $map_between_objects = array
	(
		"typeEqual" => "_eq_type",
		"typeIn" => "_in_type",
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), self::$order_by_map);
	}

	/**
	 * @var VidiunDistributionProviderType
	 */
	public $typeEqual;

	/**
	 * @dynamicType VidiunDistributionProviderType
	 * @var string
	 */
	public $typeIn;
}

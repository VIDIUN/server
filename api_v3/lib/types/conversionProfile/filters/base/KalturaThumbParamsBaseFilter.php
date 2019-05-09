<?php
/**
 * @package api
 * @relatedService ignore
 * @subpackage filters.base
 * @abstract
 */
abstract class VidiunThumbParamsBaseFilter extends VidiunAssetParamsFilter
{
	static private $map_between_objects = array
	(
		"formatEqual" => "_eq_format",
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
	 * @var VidiunContainerFormat
	 */
	public $formatEqual;
}

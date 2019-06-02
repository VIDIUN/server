<?php
/**
 * @package plugins.document
 * @relatedService ignore
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunPdfFlavorParamsOutputBaseFilter extends VidiunFlavorParamsOutputFilter
{
	static private $map_between_objects = array
	(
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
}

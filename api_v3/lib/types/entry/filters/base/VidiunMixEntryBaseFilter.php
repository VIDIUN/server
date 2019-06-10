<?php
/**
 * @package api
 * @relatedService BaseEntryService
 * @subpackage filters.base
 * @abstract
 */
abstract class VidiunMixEntryBaseFilter extends VidiunPlayableEntryFilter
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

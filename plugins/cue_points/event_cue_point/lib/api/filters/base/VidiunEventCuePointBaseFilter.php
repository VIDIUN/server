<?php
/**
 * @package plugins.eventCuePoint
 * @relatedService CuePointService
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunEventCuePointBaseFilter extends VidiunCuePointFilter
{
	static private $map_between_objects = array
	(
		"eventTypeEqual" => "_eq_event_type",
		"eventTypeIn" => "_in_event_type",
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
	 * @var VidiunEventType
	 */
	public $eventTypeEqual;

	/**
	 * @dynamicType VidiunEventType
	 * @var string
	 */
	public $eventTypeIn;
}

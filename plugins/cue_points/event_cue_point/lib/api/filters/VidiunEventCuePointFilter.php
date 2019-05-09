<?php
/**
 * @package plugins.eventCuePoint
 * @subpackage api.filters
 */
class VidiunEventCuePointFilter extends VidiunEventCuePointBaseFilter
{
	static private $map_between_objects = array
	(
			"eventTypeEqual" => "_eq_sub_type",
			"eventTypeIn" => "_in_sub_type",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		return parent::getTypeListResponse($pager, $responseProfile, EventCuePointPlugin::getCuePointTypeCoreValue(EventCuePointType::EVENT));
	}
}

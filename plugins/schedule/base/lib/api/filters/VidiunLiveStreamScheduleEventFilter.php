<?php
/**
 * @package plugins.schedule
 * @subpackage api.filters
 */
class VidiunLiveStreamScheduleEventFilter extends VidiunLiveStreamScheduleEventBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunScheduleEventFilter::getListResponseType()
	 */
	protected function getListResponseType()
	{
		return ScheduleEventType::LIVE_STREAM;
	}
}

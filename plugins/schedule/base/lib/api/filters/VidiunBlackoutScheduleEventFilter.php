<?php
/**
 * @package plugins.schedule
 * @subpackage api.filters
 */
class VidiunBlackoutScheduleEventFilter extends VidiunRecordScheduleEventBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunScheduleEventFilter::getListResponseType()
	 */
	protected function getListResponseType()
	{
		return ScheduleEventType::BLACKOUT;
	}
}

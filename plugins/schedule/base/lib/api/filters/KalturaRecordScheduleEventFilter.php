<?php
/**
 * @package plugins.schedule
 * @subpackage api.filters
 */
class VidiunRecordScheduleEventFilter extends VidiunRecordScheduleEventBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunScheduleEventFilter::getListResponseType()
	 */
	protected function getListResponseType()
	{
		return ScheduleEventType::RECORD;
	}
}

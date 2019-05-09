<?php
/**
 * @package plugins.schedule
 * @subpackage api.filters
 */
class VidiunLiveEntryScheduleResourceFilter extends VidiunLiveEntryScheduleResourceBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunScheduleResourceFilter::getListResponseType()
	 */
	protected function getListResponseType()
	{
		return ScheduleResourceType::LIVE_ENTRY;
	}
}

<?php
/**
 * @package plugins.schedule
 * @subpackage api.filters
 */
class VidiunLocationScheduleResourceFilter extends VidiunLocationScheduleResourceBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunScheduleResourceFilter::getListResponseType()
	 */
	protected function getListResponseType()
	{
		return ScheduleResourceType::LOCATION;
	}
}

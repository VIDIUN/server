<?php
/**
 * @package plugins.schedule
 * @subpackage api.filters
 */
class VidiunCameraScheduleResourceFilter extends VidiunCameraScheduleResourceBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunScheduleResourceFilter::getListResponseType()
	 */
	protected function getListResponseType()
	{
		return ScheduleResourceType::CAMERA;
	}
}

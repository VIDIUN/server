<?php
/**
 * @package plugins.scheduledTask
 * @subpackage api.filters
 */
class VidiunScheduledTaskProfileFilter extends VidiunScheduledTaskProfileBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new ScheduledTaskProfileFilter();
	}
}

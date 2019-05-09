<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.filters
 */
class VidiunEventNotificationTemplateFilter extends VidiunEventNotificationTemplateBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new EventNotificationTemplateFilter();
	}
}

<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunWidgetFilter extends VidiunWidgetBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new widgetFilter();
	}
}

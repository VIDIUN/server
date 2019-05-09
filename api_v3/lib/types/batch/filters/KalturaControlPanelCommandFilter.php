<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunControlPanelCommandFilter extends VidiunControlPanelCommandBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new ControlPanelCommandFilter();
	}
}

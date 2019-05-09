<?php
/**
 * @package plugins.virusScan
 * @subpackage api.filters
 */
class VidiunVirusScanProfileFilter extends VidiunVirusScanProfileBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new VirusScanProfileFilter();
	}
}

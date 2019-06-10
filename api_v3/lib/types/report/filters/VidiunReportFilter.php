<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunReportFilter extends VidiunReportBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new ReportFilter();
	}
}

<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.filters
 */
class VidiunDistributionProfileFilter extends VidiunDistributionProfileBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new DistributionProfileFilter();
	}
}

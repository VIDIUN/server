<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.filters
 */
class VidiunDistributionProviderFilter extends VidiunDistributionProviderBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		throw new Exception("Distribution providers can't be filtered");
	}
}

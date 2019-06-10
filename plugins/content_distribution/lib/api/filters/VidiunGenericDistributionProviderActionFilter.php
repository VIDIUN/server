<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.filters
 */
class VidiunGenericDistributionProviderActionFilter extends VidiunGenericDistributionProviderActionBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new GenericDistributionProviderActionFilter();
	}
}

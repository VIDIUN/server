<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunBaseSyndicationFeedFilter extends VidiunBaseSyndicationFeedBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new syndicationFeedFilter();
	}
}

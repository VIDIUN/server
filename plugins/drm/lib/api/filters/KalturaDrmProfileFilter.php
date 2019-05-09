<?php
/**
 * @package plugins.drm
 * @subpackage api.filters
 */
class VidiunDrmProfileFilter extends VidiunDrmProfileBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new DrmProfileFilter();
	}
}

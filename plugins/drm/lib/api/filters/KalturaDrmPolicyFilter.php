<?php
/**
 * @package plugins.drm
 * @subpackage api.filters
 */
class VidiunDrmPolicyFilter extends VidiunDrmPolicyBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new DrmPolicyFilter();
	}
}

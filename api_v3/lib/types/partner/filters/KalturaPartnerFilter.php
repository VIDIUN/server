<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunPartnerFilter extends VidiunPartnerBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new partnerFilter();
	}
}

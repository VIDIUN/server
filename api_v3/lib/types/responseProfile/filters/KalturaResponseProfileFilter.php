<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunResponseProfileFilter extends VidiunResponseProfileBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new ResponseProfileFilter();
	}
}

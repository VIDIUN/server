<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunUploadTokenFilter extends VidiunUploadTokenBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new UploadTokenFilter();
	}
}

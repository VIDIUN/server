<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunStorageProfileFilter extends VidiunStorageProfileBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new StorageProfileFilter();
	}
}

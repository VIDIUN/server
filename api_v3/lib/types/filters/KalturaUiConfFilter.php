<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunUiConfFilter extends VidiunUiConfBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new uiConfFilter();
	}
}

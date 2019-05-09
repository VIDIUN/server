<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunLiveEntryServerNodeFilter extends VidiunLiveEntryServerNodeBaseFilter
{
	public function __construct()
	{
		$this->serverTypeIn = array(VidiunEntryServerNodeType::LIVE_PRIMARY, VidiunEntryServerNodeType::LIVE_BACKUP);
	}
}

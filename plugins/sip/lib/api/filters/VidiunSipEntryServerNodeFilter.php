<?php
/**
 * @package plugins.sip
 * @subpackage api.filters
 */
class VidiunSipEntryServerNodeFilter extends VidiunSipEntryServerNodeBaseFilter
{
	public function __construct()
	{
		$this->serverTypeIn = SipPlugin::getApiValue(SipEntryServerNodeType::SIP_ENTRY_SERVER );
	}
}

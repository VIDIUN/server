<?php
/**
 * @package plugins.conference
 * @subpackage api.filters
 */
class VidiunConferenceEntryServerNodeFilter extends VidiunConferenceEntryServerNodeBaseFilter
{
	public function __construct()
	{
		$this->serverTypeIn = ConferencePlugin::getApiValue(ConferenceEntryServerNodeType::CONFERENCE_ENTRY_SERVER );
	}
}

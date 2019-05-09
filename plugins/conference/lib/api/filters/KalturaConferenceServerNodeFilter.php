<?php
/**
 * @package plugins.conference
 * @subpackage api.filters
 */
class VidiunConferenceServerNodeFilter extends VidiunConferenceServerNodeBaseFilter
{
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if(!$type)
			$type = ConferencePlugin::getCoreValue('serverNodeType',ConferenceServerNodeType::CONFERENCE_SERVER);
	
		return parent::getTypeListResponse($pager, $responseProfile, $type);
	}
}

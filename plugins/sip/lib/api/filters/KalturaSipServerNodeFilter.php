<?php
/**
 * @package plugins.sip
 * @subpackage api.filters
 */
class VidiunSipServerNodeFilter extends VidiunSipServerNodeBaseFilter
{
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if(!$type)
		{
			$type = SipPlugin::getCoreValue('serverNodeType',SipServerNodeType::SIP_SERVER);
		}
	
		return parent::getTypeListResponse($pager, $responseProfile, $type);
	}
}

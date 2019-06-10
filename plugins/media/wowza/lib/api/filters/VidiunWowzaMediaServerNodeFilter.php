<?php
/**
 * @package plugins.wowza
 * @subpackage api.filters
 */
class VidiunWowzaMediaServerNodeFilter extends VidiunWowzaMediaServerNodeBaseFilter
{
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if(!$type)
			$type = WowzaPlugin::getWowzaMediaServerTypeCoreValue(WowzaMediaServerNodeType::WOWZA_MEDIA_SERVER);
	
		return parent::getTypeListResponse($pager, $responseProfile, $type);
	}
}

<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunEdgeServerNodeFilter extends VidiunEdgeServerNodeBaseFilter
{
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		if(!$type)
			$type = serverNodeType::EDGE;
	
		return parent::getTypeListResponse($pager, $responseProfile, $type);
	}
}

<?php
/**
 * @package plugins.viewHistory
 * @subpackage api.filters
 */
class VidiunViewHistoryUserEntryFilter extends VidiunUserEntryFilter
{
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$this->typeEqual = ViewHistoryPlugin::getApiValue(ViewHistoryUserEntryType::VIEW_HISTORY);
		$response = parent::getListResponse($pager, $responseProfile);
		
		return $response;
	}
	
	public function toObject ($object_to_fill = null, $props_to_skip = array())
	{
		if (vCurrentContext::getCurrentSessionType() == SessionType::USER)
		{
			$this->userIdEqual = vCurrentContext::getCurrentVsVuser()->getPuserId();
			$this->userIdIn = null;
			$this->userIdNotIn = null;
		}
		elseif (!$this->userIdEqual && !$this->userIdIn && !$this->userIdNotIn)
		{
			$this->userIdEqual = vCurrentContext::getCurrentVsVuser() ? vCurrentContext::getCurrentVsVuser()->getPuserId() : null;
		}
		
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}

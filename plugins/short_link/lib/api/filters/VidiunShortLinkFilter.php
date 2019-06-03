<?php
/**
 * @package plugins.shortLink
 * @subpackage api.filters
 */
class VidiunShortLinkFilter extends VidiunShortLinkBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new ShortLinkFilter();
	}
	
	public function toFilter($partnerId)
	{
		if(!is_null($this->userIdEqual))
		{
			$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $this->userIdEqual);
			if ($vuser)
				$this->userIdEqual = $vuser->getId();
			else 
				$this->userIdEqual = -1; // no result will be returned when the user is missing
		}
	
		if(!is_null($this->userIdIn))
		{
			$puserIds = explode(',', $this->userIdIn);
			$vusers = vuserPeer::getVuserByPartnerAndUids($partnerId, $puserIds);
			if(count($vusers))
			{
				$vuserIds = array();
				foreach($vusers as $vuser)
					$vuserIds[] = $vuser->getId();
					
				$this->userIdIn = implode(',', $vuserIds);
			}
			else
			{
				$this->userIdIn = -1; // no result will be returned when the user is missing
			}
		}

		return parent::toObject();
	}	
}

<?php
class vPartnerBroadcastUrlManager extends vBroadcastUrlManager
{
	protected function getHostName($dc, $primary, $entry, $protocol)
	{
		$partner  = PartnerPeer::retrieveByPK($this->partnerId);
		if (!$partner)
		{
			VidiunLog::info("Partner with id [{$this->partnerId}] was not found");
			return null;
		}

		if($primary)
			return $partner->getPrimaryBroadcastUrl();

		return $partner->getSecondaryBroadcastUrl();
	}

}
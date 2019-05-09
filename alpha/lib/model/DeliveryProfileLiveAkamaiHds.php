<?php

class DeliveryProfileLiveAkamaiHds extends DeliveryProfileLiveHds {
	
	public function checkIsLive ($url)
	{
		$url = vDeliveryUtils::addQueryParameter($url, "hdcore=" . vConf::get('hd_core_version'));
		return parent::checkIsLive($url);
	}
}


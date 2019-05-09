<?php

class DeliveryProfileLivePackagerHls extends DeliveryProfileLiveAppleHttp {
	
	protected function getHttpUrl($serverNode)
	{
		$httpUrl = $this->getLivePackagerUrl($serverNode, PlaybackProtocol::HLS);
		
		$httpUrl .= "master";
		
		foreach($this->getDynamicAttributes()->getFlavorParamIds() as $flavorId)
		{
			$httpUrl .= "-s$flavorId";
		}
		
		$httpUrl .= ".m3u8";
		
		VidiunLog::debug("Live Stream url [$httpUrl]");
		return $httpUrl;
	}
	
	protected function getUrlPrefix($url, $vLiveStreamParams)
	{
		return requestUtils::resolve("index-s" . $vLiveStreamParams->getFlavorId() . ".m3u8" , $url);
	}
}

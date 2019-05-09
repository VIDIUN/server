<?php

class DeliveryProfileLivePackagerMss extends DeliveryProfileLive
{
	function __construct()
	{
		parent::__construct();
		$this->DEFAULT_RENDERER_CLASS = 'vRedirectManifestRenderer';
	}
	
	protected function getHttpUrl($serverNode)
	{
		$httpUrl = $this->getLivePackagerUrl($serverNode);
		$httpUrl .= "manifest";
		
		foreach($this->getDynamicAttributes()->getFlavorParamIds() as $flavorId)
		{
			$httpUrl .= "-s$flavorId";
		}
		
		VidiunLog::debug("Live Stream url [$httpUrl]");
		return $httpUrl;
	}
}


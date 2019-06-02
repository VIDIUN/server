<?php

class DeliveryProfileHttp extends DeliveryProfileVod {
	
	function __construct() {
		parent::__construct();
		$this->DEFAULT_RENDERER_CLASS = 'vF4MManifestRenderer';
	}
	
	protected function doGetFlavorAssetUrl(asset $flavorAsset)
	{
		$url = parent::doGetFlavorAssetUrl($flavorAsset);
		if($this->params->getFileExtension())
			$url .= "/name/a.".$this->params->getFileExtension();
		if($this->params->getSeekFromTime() > 0)
			$url .= "/seekFrom/" . $this->params->getSeekFromTime();
		return $url;
	}
	
	// doGetFileSyncUrl - Inherit from parent
	
	/**
	 * @return array $flavors
	 */
	public function buildServeFlavors()
	{
		$flavors = $this->buildHttpFlavorsArray();
		
		return $flavors;
	}
	
}


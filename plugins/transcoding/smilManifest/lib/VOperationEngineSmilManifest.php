<?php

/**
 * @package plugins.smilManifest
 * @subpackage lib
 */
class VOperationEngineSmilManifest extends VSingleOutputOperationEngine
{
	/* (non-PHPdoc)
	 * @see VOperationEngine::getCmdLine()
	 */
	protected function getCmdLine() {}

	/*
	 * (non-PHPdoc)
	 * @see VOperationEngine::doOperation()
	 * 
	 * 
	 */
	protected function doOperation()
	{
		if(!$this->data->srcFileSyncs)
			return true;

		$smilTemplate = $this->getSmilTemplate();
		$xpath = new DOMXPath($smilTemplate);
		$wrapperElement = $xpath->query('/smil/body/switch')->item(0);
		foreach($this->data->srcFileSyncs as $srcFileSync)
		{
			/** @var VidiunSourceFileSyncDescriptor $srcFileSync */
			$fileName = pathinfo($srcFileSync->actualFileSyncLocalPath, PATHINFO_BASENAME);
			$bitrate = $this->getBitrateForAsset($srcFileSync->assetId);
			$this->addSmilVideo($wrapperElement, $fileName, $bitrate);
		}

		$smilFilePath = $this->outFilePath.".smil";
		$smilData = $smilTemplate->saveXML();
		file_put_contents($smilFilePath, $smilData);

		$destFileSyncDescArr = array();
		$fileSyncDesc = new VidiunDestFileSyncDescriptor();
		$fileSyncDesc->fileSyncLocalPath = $smilFilePath;
		$fileSyncDesc->fileSyncObjectSubType = 1; // asset;
		$destFileSyncDescArr[] = $fileSyncDesc;

		$this->data->extraDestFileSyncs  = $destFileSyncDescArr;

		$this->data->destFileSyncLocalPath = null;
		$this->outFilePath = null;

		return true;
	}

	protected function addSmilVideo(DOMElement $wrapperElement, $fileName, $bitrate)
	{
		$videoElement = $wrapperElement->ownerDocument->createElement('video');
		$videoElement->setAttribute('src', $fileName);
		$videoElement->setAttribute('system-bitrate', $bitrate * 1024);
		$wrapperElement->appendChild($videoElement);
	}

	protected function getSmilTemplate()
	{
		$xmlData = '<smil>
						<head>
						</head>
						<body>
							<switch>
							</switch>
						</body>
					</smil>
					';
		$doc = new DOMDocument();
		$doc->loadXML($xmlData);
		return $doc;
	}

	protected function getBitrateForAsset($assetId)
	{
		if (!$this->data->pluginData)
			return null;

		foreach($this->data->pluginData as $pluginData)
		{
			/** @var VidiunKeyValue $pluginData */
			if ($pluginData->key == 'asset_'.$assetId.'_bitrate')
				return $pluginData->value;
		}

		return null;
	}
}
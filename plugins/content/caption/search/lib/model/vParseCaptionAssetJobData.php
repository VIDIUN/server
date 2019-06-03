<?php
/**
 * @package plugins.virusScan
 * @subpackage model.data
 */
class vParseCaptionAssetJobData extends vJobData
{
	/**
	 * @var string
	 */
	private $captionAssetId;
	
	/**
	 * @return string $captionAssetId
	 */
	public function getCaptionAssetId()
	{
		return $this->captionAssetId;
	}

	/**
	 * @param string $captionAssetId
	 */
	public function setCaptionAssetId($captionAssetId)
	{
		$this->captionAssetId = $captionAssetId;
	}
}

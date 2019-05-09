<?php
/**
 * @package plugins.youtubeApiDistribution
 * @subpackage model.data
 */
class vYoutubeApiDistributionJobProviderData extends vConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	private $videoAssetFilePath;
	
	/**
	 * @var string
	 */
	private $thumbAssetFilePath;
	
	/**
	 * @var VidiunYouTubeApiCaptionDistributionInfoArray
	 */
	private $captionsInfo;
	
	/**
	 * @var int
	 */
	private $distributionProfileId;

	/**
	 * @return the $distributionProfileId
	 */
	public function getDistributionProfileId()
	{
		return $this->distributionProfileId;
	}

	/**
	 * @param int $distributionProfileId
	 */
	public function setDistributionProfileId($distributionProfileId)
	{
		$this->distributionProfileId = $distributionProfileId;
	}

	/**
	 * @return string $videoAssetFilePath
	 */
	public function getVideoAssetFilePath()
	{
		return $this->videoAssetFilePath;
	}

	/**
	 * @param string $videoAssetFilePath
	 */
	public function setVideoAssetFilePath($videoAssetFilePath)
	{
		$this->videoAssetFilePath = $videoAssetFilePath;
	}
	
	/**
	 * @return string $thumbAssetFilePath
	 */
	public function getThumbAssetFilePath()
	{
		return $this->thumbAssetFilePath;
	}

	/**
	 * @param string $thumbAssetFilePath
	 */
	public function setThumbAssetFilePath($thumbAssetFilePath)
	{
		$this->thumbAssetFilePath = $thumbAssetFilePath;
	}	
	
	/**
	 * @return VidiunYouTubeApiCaptionDistributionInfoArray $captionsInfo
	 */
	public function getCaptionsInfo()
	{
		return $this->captionsInfo;
	}

	/**
	 * @param VidiunYouTubeApiCaptionDistributionInfoArray $captionsInfo
	 */
	public function setCaptionsInfo($captionsInfo)
	{
		$this->captionsInfo = $captionsInfo;
	}	
	
    
	public function __construct(vDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
	}
}
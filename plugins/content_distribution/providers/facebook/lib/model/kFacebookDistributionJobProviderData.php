<?php
/**
 * @package plugins.facebookDistribution
 * @subpackage model.data
 */
class vFacebookDistributionJobProviderData extends vConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	private $videoAssetFilePath;
	
	/**
	 * @var string
	 */
	private $thumbAssetId;
	
	/**
	 * @var VidiunFacebookCaptionDistributionInfoArray
	 */
	private $captionsInfo;
	
	/**
	 * @var int
	 */
	private $distributionProfileId;

	/**
	 * @return int $distributionProfileId
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
	public function getThumbAssetId()
	{
		return $this->thumbAssetId;
	}

	/**
	 * @param string $thumbAssetId
	 */
	public function setThumbAssetId($thumbAssetId)
	{
		$this->thumbAssetId = $thumbAssetId;
	}	
	
	/**
	 * @return VidiunFacebookCaptionDistributionInfoArray $captionsInfo
	 */
	public function getCaptionsInfo()
	{
		return $this->captionsInfo;
	}

	/**
	 * @param VidiunFacebookCaptionDistributionInfoArray $captionsInfo
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
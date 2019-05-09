<?php
/**
 * @package plugins.uverseDistribution
 * @subpackage model.data
 */
class vUverseDistributionJobProviderData extends vDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $remoteAssetUrl;
	
	/**
	 * @var string
	 */
	public $remoteAssetFileName;
	
	public function getRemoteAssetUrl ()
	{
		return $this->remoteAssetUrl;
	}

	public function getRemoteAssetFileName ()
	{
		return $this->remoteAssetFileName;
	}

	public function setRemoteAssetUrl ($remoteAssetUrl)
	{
		$this->remoteAssetUrl = $remoteAssetUrl;
	}

	public function setRemoteAssetFileName ($remoteAssetFileName)
	{
		$this->remoteAssetFileName = $remoteAssetFileName;
	}

	public function __construct(vDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
	}
}
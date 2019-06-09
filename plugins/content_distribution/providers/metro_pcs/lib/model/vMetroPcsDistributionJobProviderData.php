<?php
/**
 * @package plugins.metroPcsDistribution
 * @subpackage model.data
 */
class vMetroPcsDistributionJobProviderData extends vDistributionJobProviderData
{
		
	/**
	 * @var string
	 */
	public $assetLocalPaths;
	
	/**
	 * @var string
	 */
	public $thumbUrls;
	
	
	public function __construct(vDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
	}
	

	/**
	 * @return the $assetLocalPaths
	 */
	public function getAssetLocalPaths() {
		return $this->assetLocalPaths;
	}

	/**
	 * @param string $assetLocalPaths
	 */
	public function setAssetLocalPaths($assetLocalPaths) {
		$this->assetLocalPaths = $assetLocalPaths;
	}

	/**
	 * @return the $thumbUrls
	 */
	public function getThumbUrls() {
		return $this->thumbUrls;
	}

	/**
	 * @param string $thumbUrls
	 */
	public function setThumbUrls($thumbUrls) {
		$this->thumbUrls = $thumbUrls;
	}

	
	
}
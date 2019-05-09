<?php
/**
 * @package plugins.quickPlayDistribution
 * @subpackage model.data
 */
class vQuickPlayDistributionJobProviderData extends vDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $xml;
	
	public function getXml()
	{
		return $this->xml;
	}

	public function setXml($xml)
	{
		$this->xml = $xml;
	}

	public function __construct(vDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
	}
}
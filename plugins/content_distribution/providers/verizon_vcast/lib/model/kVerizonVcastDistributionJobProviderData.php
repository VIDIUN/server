<?php
/**
 * @package plugins.verizonVcastDistribution
 * @subpackage model.data
 */
class vVerizonVcastDistributionJobProviderData extends vDistributionJobProviderData
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
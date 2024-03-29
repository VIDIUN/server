<?php
/**
 * @package plugins.timeWarnerDistribution
 * @subpackage model.data
 */
class vTimeWarnerDistributionJobProviderData extends vDistributionJobProviderData
{
	/**
	 * @var string
	 */
	private $xml;
		
	
	/**
	 * @var int
	 */
	private $distributionProfileId;

	public function __construct(vDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
	}

	
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
	 * @return the $xml
	 */
	public function getXml()
	{
		return $this->xml;
	}


	/**
	 * @param string $xml
	 */
	public function setXml($xml)
	{
		$this->xml = $xml;
	}
}
<?php
/**
 * @package plugins.tvinciDistribution
 * @subpackage model.data
 */
class vTvinciDistributionJobProviderData extends vConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $xml;

	public function setXml($xml)	{ $this->xml = $xml; }
	public function getXml()		{ return $this->xml; }
	
	public function __construct(vDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
	}
}
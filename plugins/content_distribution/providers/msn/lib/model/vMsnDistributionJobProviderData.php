<?php
/**
 * @package plugins.msnDistribution
 * @subpackage model.data
 */
class vMsnDistributionJobProviderData extends vDistributionJobProviderData
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
}
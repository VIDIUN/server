<?php
/**
 * @package plugins.contentDistribution
 * @subpackage model.data
 */
class vGenericDistributionJobProviderData extends vDistributionJobProviderData
{
	/**
	 * @var string
	 */
	private $xml;
	
	/**
	 * @var string
	 */
	private $resultParseData;
	
	/**
	 * @var VidiunGenericDistributionProviderParser
	 */
	private $resultParserType;

	public function __construct(vDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
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
	
	/**
	 * @return the $resultParseData
	 */
	public function getResultParseData()
	{
		return $this->resultParseData;
	}

	/**
	 * @return the $resultParserType
	 */
	public function getResultParserType()
	{
		return $this->resultParserType;
	}

	/**
	 * @param string $resultParseData
	 */
	public function setResultParseData($resultParseData)
	{
		$this->resultParseData = $resultParseData;
	}

	/**
	 * @param VidiunGenericDistributionProviderParser $resultParserType
	 */
	public function setResultParserType($resultParserType)
	{
		$this->resultParserType = $resultParserType;
	}
}
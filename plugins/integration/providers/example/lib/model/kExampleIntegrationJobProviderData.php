<?php
/**
 * @package plugins.exampleIntegration
 * @subpackage model.data
 */
class vExampleIntegrationJobProviderData extends vIntegrationJobProviderData
{
	/**
	 * @var string
	 */
	private $exampleUrl;
	
	/**
	 * @return string
	 */
	public function getExampleUrl()
	{
		return $this->exampleUrl;
	}

	/**
	 * @param string $exampleUrl
	 */
	public function setExampleUrl($exampleUrl)
	{
		$this->exampleUrl = $exampleUrl;
	}
}
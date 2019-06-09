<?php
/**
 * @package plugins.integration
 * @subpackage model.data
 */
class vIntegrationJobData extends vJobData
{
	/**
	 * @var string
	 */
	private $callbackNotificationUrl;

	/**
	 * @var vIntegrationJobProviderData
	 */
	private $providerData;
	
	/**
	 * @var IntegrationProviderType
	 */
	private $providerType;
	
	/**
	 * @var vIntegrationJobTriggerData
	 */
	private $triggerData;
	
	/**
	 * @var IntegrationTriggerType
	 */
	private $triggerType;
	
	/**
	 * @return string
	 */
	public function getCallbackNotificationUrl()
	{
		return $this->callbackNotificationUrl;
	}
	
	/**
	 * @param string $callbackNotificationUrl
	 */
	public function setCallbackNotificationUrl($callbackNotificationUrl)
	{
		$this->callbackNotificationUrl = $callbackNotificationUrl;
	}
	
	/**
	 * @return IntegrationProviderType
	 */
	public function getProviderType()
	{
		return $this->providerType;
	}

	/**
	 * @param IntegrationProviderType $providerType
	 */
	public function setProviderType($providerType)
	{
		$this->providerType = $providerType;
	}

	/**
	 * @return vIntegrationJobProviderData
	 */
	public function getProviderData()
	{
		return $this->providerData;
	}

	/**
	 * @param vIntegrationJobProviderData $providerData
	 */
	public function setProviderData(vIntegrationJobProviderData $providerData)
	{
		$this->providerData = $providerData;
	}
	
	/**
	 * @return IntegrationTriggerType
	 */
	public function getTriggerType()
	{
		return $this->triggerType;
	}

	/**
	 * @param IntegrationTriggerType $triggerType
	 */
	public function setTriggerType($triggerType)
	{
		$this->triggerType = $triggerType;
	}

	/**
	 * @return vIntegrationJobTriggerData
	 */
	public function getTriggerData()
	{
		return $this->triggerData;
	}

	/**
	 * @param vIntegrationJobTriggerData $triggerData
	 */
	public function setTriggerData(vIntegrationJobTriggerData $triggerData)
	{
		$this->triggerData = $triggerData;
	}
}
<?php
/**
 * @package plugins.bpmEventNotificationIntegration
 */
class BpmEventNotificationIntegrationPlugin extends VidiunPlugin implements IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'bpmEventNotificationIntegration';
	const FLOW_MANAGER = 'vBpmEventNotificationIntegrationFlowManager';
	
	const INTEGRATION_PLUGIN_VERSION_MAJOR = 1;
	const INTEGRATION_PLUGIN_VERSION_MINOR = 0;
	const INTEGRATION_PLUGIN_VERSION_BUILD = 0;
	
	const BPM_PLUGIN_VERSION_MAJOR = 1;
	const BPM_PLUGIN_VERSION_MINOR = 0;
	const BPM_PLUGIN_VERSION_BUILD = 0;

	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$integrationVersion = new VidiunVersion(
			self::INTEGRATION_PLUGIN_VERSION_MAJOR,
			self::INTEGRATION_PLUGIN_VERSION_MINOR,
			self::INTEGRATION_PLUGIN_VERSION_BUILD
		);
		$integrationDependency = new VidiunDependency(IntegrationPlugin::getPluginName(), $integrationVersion);
		
		$bpmVersion = new VidiunVersion(
			self::BPM_PLUGIN_VERSION_MAJOR,
			self::BPM_PLUGIN_VERSION_MINOR,
			self::BPM_PLUGIN_VERSION_BUILD
		);
		$bpmDependency = new VidiunDependency(BusinessProcessNotificationPlugin::getPluginName(), $bpmVersion);
		
		return array($integrationDependency, $bpmDependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::FLOW_MANAGER,
		);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('BpmEventNotificationIntegrationTrigger');
	
		if($baseEnumName == 'IntegrationTriggerType')
			return array('BpmEventNotificationIntegrationTrigger');
			
		return array();
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{			
		$objectClass = self::getObjectClass($baseClass, $enumValue);
		if (is_null($objectClass)) 
		{
			return null;
		}
		
		if (!is_null($constructorArgs))
		{
			$reflect = new ReflectionClass($objectClass);
			return $reflect->newInstanceArgs($constructorArgs);
		}
		else
		{
			return new $objectClass();
		}
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'vIntegrationJobTriggerData' && $enumValue == self::getApiValue(BpmEventNotificationIntegrationTrigger::BPM_EVENT_NOTIFICATION))
		{
			return 'vBpmEventNotificationIntegrationJobTriggerData';
		}
	
		if($baseClass == 'VidiunIntegrationJobTriggerData')
		{
			if($enumValue == self::getApiValue(BpmEventNotificationIntegrationTrigger::BPM_EVENT_NOTIFICATION) || $enumValue == self::getIntegrationTriggerCoreValue(BpmEventNotificationIntegrationTrigger::BPM_EVENT_NOTIFICATION))
				return 'VidiunBpmEventNotificationIntegrationJobTriggerData';
		}
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getIntegrationTriggerCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('IntegrationTriggerType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}

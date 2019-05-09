<?php
/**
 * Extension plugin for scheduled task plugin to add support for distributing content
 *
 * @package plugins.scheduledTaskEventNotification
 */
class ScheduledTaskContentDistributionPlugin extends VidiunPlugin implements IVidiunPending, IVidiunEnumerator, IVidiunObjectLoader
{
	const PLUGIN_NAME = 'scheduledTaskContentDistribution';
	
	const SCHEDULED_TASK_PLUGIN_NAME = 'scheduledTask';
	
	const CONTENT_DISTRIBUTION_PLUGIN_NAME = 'contentDistribution';
	const CONTENT_DISTRIBUTION_PLUGIN_VERSION_MAJOR = 1;
	const CONTENT_DISTRIBUTION_PLUGIN_VERSION_MINOR = 0;
	const CONTENT_DISTRIBUTION_PLUGIN_VERSION_BUILD = 0;
	
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
		$eventNotificationVersion = new VidiunVersion(self::CONTENT_DISTRIBUTION_PLUGIN_VERSION_MAJOR, self::CONTENT_DISTRIBUTION_PLUGIN_VERSION_MINOR, self::CONTENT_DISTRIBUTION_PLUGIN_VERSION_BUILD);
		
		$scheduledTaskDependency = new VidiunDependency(self::SCHEDULED_TASK_PLUGIN_NAME);
		$eventNotificationDependency = new VidiunDependency(self::CONTENT_DISTRIBUTION_PLUGIN_NAME, $eventNotificationVersion);
		
		return array($scheduledTaskDependency, $eventNotificationDependency);
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('DistributeObjectTaskType');
	
		if($baseEnumName == 'ObjectTaskType')
			return array('DistributeObjectTaskType');
			
		return array();
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if (class_exists('Vidiun_Client_Client'))
			return null;

		if (class_exists('VidiunClient'))
		{
			if ($baseClass == 'VObjectTaskEntryEngineBase' && $enumValue == VidiunObjectTaskType::DISTRIBUTE)
				return new VObjectTaskDistributeEngine();
		}
		else
		{
			$apiValue = self::getApiValue(DistributeObjectTaskType::DISTRIBUTE);
			$distributeObjectTaskCoreValue = vPluginableEnumsManager::apiToCore('ObjectTaskType', $apiValue);
			if($baseClass == 'VidiunObjectTask' && $enumValue == $distributeObjectTaskCoreValue)
				return new VidiunDistributeObjectTask();

			if ($baseClass == 'VObjectTaskEntryEngineBase' && $enumValue == $apiValue)
				return new VObjectTaskDistributeEngine();
		}

		return null;
	}
		
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		return null;
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}

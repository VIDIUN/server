<?php
/**
 * Extension plugin for scheduled task plugin to add support to dispatch event notification object task
 *
 * @package plugins.scheduledTaskEventNotification
 */
class ScheduledTaskEventNotificationPlugin extends VidiunPlugin implements IVidiunPending, IVidiunEnumerator, IVidiunObjectLoader
{
	const PLUGIN_NAME = 'scheduledTaskEventNotification';
	
	const SCHEDULED_TASK_PLUGIN_NAME = 'scheduledTask';
	
	const EVENT_NOTIFICATION_PLUGIN_NAME = 'eventNotification';
	const EVENT_NOTIFICATION_PLUGIN_VERSION_MAJOR = 1;
	const EVENT_NOTIFICATION_PLUGIN_VERSION_MINOR = 0;
	const EVENT_NOTIFICATION_PLUGIN_VERSION_BUILD = 0;
	
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
		$eventNotificationVersion = new VidiunVersion(self::EVENT_NOTIFICATION_PLUGIN_VERSION_MAJOR, self::EVENT_NOTIFICATION_PLUGIN_VERSION_MINOR, self::EVENT_NOTIFICATION_PLUGIN_VERSION_BUILD);
		
		$scheduledTaskDependency = new VidiunDependency(self::SCHEDULED_TASK_PLUGIN_NAME);
		$eventNotificationDependency = new VidiunDependency(self::EVENT_NOTIFICATION_PLUGIN_NAME, $eventNotificationVersion);
		
		return array($scheduledTaskDependency, $eventNotificationDependency);
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('DispatchEventNotificationObjectTaskType');
	
		if($baseEnumName == 'ObjectTaskType')
			return array('DispatchEventNotificationObjectTaskType');
			
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
			if ($baseClass == 'VObjectTaskEntryEngineBase' && $enumValue == VidiunObjectTaskType::DISPATCH_EVENT_NOTIFICATION)
				return new VObjectTaskDispatchEventNotificationEngine();
		}
		else
		{
			$apiValue = self::getApiValue(DispatchEventNotificationObjectTaskType::DISPATCH_EVENT_NOTIFICATION);
			$dispatchEventNotificationObjectTaskCoreValue = vPluginableEnumsManager::apiToCore('ObjectTaskType', $apiValue);
			if($baseClass == 'VidiunObjectTask' && $enumValue == $dispatchEventNotificationObjectTaskCoreValue)
				return new VidiunDispatchEventNotificationObjectTask();

			if ($baseClass == 'VObjectTaskEntryEngineBase' && $enumValue == $apiValue)
				return new VObjectTaskDispatchEventNotificationEngine();
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

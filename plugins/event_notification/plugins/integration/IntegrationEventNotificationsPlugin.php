<?php
/**
 * Enable event notifications on content distribution objects
 * @package plugins.integrationEventNotifications
 */
class IntegrationEventNotificationsPlugin extends VidiunPlugin implements IVidiunPending, IVidiunEnumerator
{
	const PLUGIN_NAME = 'integrationEventNotifications';
	
	const INTEGRATION_PLUGIN_NAME = 'integration';
	
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
		
		$integrationDependency = new VidiunDependency(self::INTEGRATION_PLUGIN_NAME);
		$eventNotificationDependency = new VidiunDependency(self::EVENT_NOTIFICATION_PLUGIN_NAME, $eventNotificationVersion);
		
		return array($integrationDependency, $eventNotificationDependency);
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('IntegrationEventNotificationEventType');
	
		if($baseEnumName == 'EventNotificationEventType')
			return array('IntegrationEventNotificationEventType');
			
		return array();
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}

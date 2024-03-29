<?php
/**
 * Enable event notifications on cue point objects
 * @package plugins.cuePointEventNotifications
 */
class CuePointEventNotificationsPlugin extends VidiunPlugin implements IVidiunPending, IVidiunEnumerator, IVidiunObjectLoader
{
	const PLUGIN_NAME = 'cuePointEventNotifications';
	
	const CUE_POINT_PLUGIN_NAME = 'cuePoint';
	const CUE_POINT_PLUGIN_VERSION_MAJOR = 1;
	const CUE_POINT_PLUGIN_VERSION_MINOR = 0;
	const CUE_POINT_PLUGIN_VERSION_BUILD = 0;
	
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
		$cuePointVersion = new VidiunVersion(self::CUE_POINT_PLUGIN_VERSION_MAJOR, self::CUE_POINT_PLUGIN_VERSION_MINOR, self::CUE_POINT_PLUGIN_VERSION_BUILD);
		$eventNotificationVersion = new VidiunVersion(self::EVENT_NOTIFICATION_PLUGIN_VERSION_MAJOR, self::EVENT_NOTIFICATION_PLUGIN_VERSION_MINOR, self::EVENT_NOTIFICATION_PLUGIN_VERSION_BUILD);
		
		$cuePointDependency = new VidiunDependency(self::CUE_POINT_PLUGIN_NAME, $cuePointVersion);
		$eventNotificationDependency = new VidiunDependency(self::EVENT_NOTIFICATION_PLUGIN_NAME, $eventNotificationVersion);
		
		return array($cuePointDependency, $eventNotificationDependency);
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('CuePointEventNotificationEventObjectType');
	
		if($baseEnumName == 'EventNotificationEventObjectType')
			return array('CuePointEventNotificationEventObjectType');
			
		return array();
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		return null;
	}
		
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'EventNotificationEventObjectType' && $enumValue == self::getEventNotificationEventObjectTypeCoreValue(CuePointEventNotificationEventObjectType::CUE_POINT))
		{
			return 'CuePoint';
		}
					
		return null;
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getEventNotificationEventObjectTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('EventNotificationEventObjectType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}

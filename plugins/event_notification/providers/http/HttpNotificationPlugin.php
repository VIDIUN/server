<?php
/**
 * @package plugins.httpNotification
 */
class HttpNotificationPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunPending, IVidiunObjectLoader, IVidiunEnumerator, IVidiunApplicationPartialView
{
	const PLUGIN_NAME = 'httpNotification';
	
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
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if ($partner)
			return $partner->getPluginEnabled(self::PLUGIN_NAME);		
			
		return false;
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('HttpNotificationTemplateType', 'HttpNotificationFileSyncObjectType');
	
		if($baseEnumName == 'EventNotificationTemplateType')
			return array('HttpNotificationTemplateType');
			
		if($baseEnumName == 'FileSyncObjectType')
			return array('HttpNotificationFileSyncObjectType');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunApplicationPartialView::getApplicationPartialViews()
	 */
	public static function getApplicationPartialViews($controller, $action)
	{
		if($controller == 'plugin' && $action == 'EventNotificationTemplateConfigureAction')
		{
			return array(
				new Vidiun_View_Helper_HttpNotificationTemplateConfigure(),
			);
		}
		
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'ISyncableFile' && $enumValue == self::getHttpNotificationFileSyncObjectTypeCoreValue(HttpNotificationFileSyncObjectType::HTTP_NOTIFICATION_TEMPLATE) && isset($constructorArgs['objectId']))
			return EventNotificationTemplatePeer::retrieveTypeByPK(self::getHttpNotificationTemplateTypeCoreValue(HttpNotificationTemplateType::HTTP), $constructorArgs['objectId']);
	
		$class = self::getObjectClass($baseClass, $enumValue);
		if($class)
		{
			if(is_array($constructorArgs))
			{
				$reflect = new ReflectionClass($class);
				return $reflect->newInstanceArgs($constructorArgs);
			}
			
			return new $class();
		}
			
		return null;
	}
		
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'VidiunEventNotificationDispatchJobData' && $enumValue == self::getHttpNotificationTemplateTypeCoreValue(HttpNotificationTemplateType::HTTP))
			return 'VidiunHttpNotificationDispatchJobData';
	
		if($baseClass == 'EventNotificationTemplate' && $enumValue == self::getHttpNotificationTemplateTypeCoreValue(HttpNotificationTemplateType::HTTP))
			return 'HttpNotificationTemplate';
	
		if($baseClass == 'VidiunEventNotificationTemplate' && $enumValue == self::getHttpNotificationTemplateTypeCoreValue(HttpNotificationTemplateType::HTTP))
			return 'VidiunHttpNotificationTemplate';
	
		if($baseClass == 'Form_EventNotificationTemplateConfiguration' && $enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::HTTP)
			return 'Form_HttpNotificationTemplateConfiguration';
	
		if($baseClass == 'Vidiun_Client_EventNotification_Type_EventNotificationTemplate' && $enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::HTTP)
			return 'Vidiun_Client_HttpNotification_Type_HttpNotificationTemplate';
	
		if($baseClass == 'VDispatchEventNotificationEngine' && $enumValue == VidiunEventNotificationTemplateType::HTTP)
			return 'VDispatchHttpNotificationEngine';
			
		return null;
	}

	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn() 
	{
		$minVersion = new VidiunVersion(
			self::EVENT_NOTIFICATION_PLUGIN_VERSION_MAJOR,
			self::EVENT_NOTIFICATION_PLUGIN_VERSION_MINOR,
			self::EVENT_NOTIFICATION_PLUGIN_VERSION_BUILD
		);
		$dependency = new VidiunDependency(self::EVENT_NOTIFICATION_PLUGIN_NAME, $minVersion);
		
		return array($dependency);
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getHttpNotificationFileSyncObjectTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('FileSyncObjectType', $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getHttpNotificationTemplateTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('EventNotificationTemplateType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}

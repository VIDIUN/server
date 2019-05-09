<?php
/**
 * @package plugins.booleanNotification
 */
class BooleanNotificationPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunPending, IVidiunObjectLoader, IVidiunEnumerator
{
	const PLUGIN_NAME = 'booleanNotification';

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
		{
			return $partner->getPluginEnabled(self::PLUGIN_NAME);
		}
		return false;
	}

	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
		{
			return array('BooleanNotificationTemplateType');
		}
		if($baseEnumName == 'EventNotificationTemplateType')
		{
			return array('BooleanNotificationTemplateType');
		}

		return array();
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if (isset($constructorArgs['objectId']))
			return EventNotificationTemplatePeer::retrieveTypeByPK(self::getBooleanNotificationTemplateTypeCoreValue(BooleanNotificationTemplateType::BOOLEAN),$constructorArgs['objectId']);
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
		if($baseClass == 'EventNotificationTemplate' && $enumValue == self::getBooleanNotificationTemplateTypeCoreValue(BooleanNotificationTemplateType::BOOLEAN))
			return 'BooleanNotificationTemplate';
		if($baseClass == 'VidiunEventNotificationTemplate' && $enumValue == self::getBooleanNotificationTemplateTypeCoreValue(BooleanNotificationTemplateType::BOOLEAN))
			return 'VidiunBooleanNotificationTemplate';
		if($baseClass == 'Form_EventNotificationTemplateConfiguration' && $enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::BOOLEAN)
			return 'Form_BooleanNotificationTemplateConfiguration';
		if($baseClass == 'Vidiun_Client_EventNotification_Type_EventNotificationTemplate' && $enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::BOOLEAN)
			return 'Vidiun_Client_BooleanNotification_Type_BooleanNotificationTemplate';
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
	public static function getBooleanNotificationTemplateTypeCoreValue($valueName)
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
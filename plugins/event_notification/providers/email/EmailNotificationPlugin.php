<?php
/**
 * @package plugins.emailNotification
 * 
 * 
 * TODO
 * Add event consumer to consume new email jobs and dispath event notification instead
 * Untill all mails are sent throgh events
 */
class EmailNotificationPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunPending, IVidiunObjectLoader, IVidiunEnumerator
{
	const PLUGIN_NAME = 'emailNotification';
	
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
			return array('EmailNotificationTemplateType', 'EmailNotificationFileSyncObjectType');
	
		if($baseEnumName == 'EventNotificationTemplateType')
			return array('EmailNotificationTemplateType');
			
		if($baseEnumName == 'FileSyncObjectType')
			return array('EmailNotificationFileSyncObjectType');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'ISyncableFile' && $enumValue == self::getEmailNotificationFileSyncObjectTypeCoreValue(EmailNotificationFileSyncObjectType::EMAIL_NOTIFICATION_TEMPLATE) && isset($constructorArgs['objectId']))
			return EventNotificationTemplatePeer::retrieveTypeByPK(self::getEmailNotificationTemplateTypeCoreValue(EmailNotificationTemplateType::EMAIL), $constructorArgs['objectId']);
		
			
		if ($baseClass == 'VEmailNotificationRecipientEngine')
		{
			list($recipientJobData) = $constructorArgs;
			switch ($enumValue)	
			{
				case VidiunEmailNotificationRecipientProviderType::CATEGORY:
					return new VEmailNotificationCategoryRecipientEngine($recipientJobData);
					break;
				case VidiunEmailNotificationRecipientProviderType::STATIC_LIST:
					return new VEmailNotificationStaticRecipientEngine($recipientJobData);
					break;
				case VidiunEmailNotificationRecipientProviderType::USER:
					return new VEmailNotificationUserRecipientEngine($recipientJobData);
					break;
				case VidiunEmailNotificationRecipientProviderType::GROUP:
					return new VEMailNotificationGroupRecipientEngine($recipientJobData);
					break;
			}
		}
		
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
		if($baseClass == 'VidiunEventNotificationDispatchJobData' && $enumValue == self::getEmailNotificationTemplateTypeCoreValue(EmailNotificationTemplateType::EMAIL))
			return 'VidiunEmailNotificationDispatchJobData';
	
		if($baseClass == 'EventNotificationTemplate' && $enumValue == self::getEmailNotificationTemplateTypeCoreValue(EmailNotificationTemplateType::EMAIL))
			return 'EmailNotificationTemplate';
	
		if($baseClass == 'VidiunEventNotificationTemplate' && $enumValue == self::getEmailNotificationTemplateTypeCoreValue(EmailNotificationTemplateType::EMAIL))
			return 'VidiunEmailNotificationTemplate';
	
		if($baseClass == 'Form_EventNotificationTemplateConfiguration' && $enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::EMAIL)
			return 'Form_EmailNotificationTemplateConfiguration';
	
		if($baseClass == 'Vidiun_Client_EventNotification_Type_EventNotificationTemplate' && $enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::EMAIL)
			return 'Vidiun_Client_EmailNotification_Type_EmailNotificationTemplate';
	
		if($baseClass == 'VDispatchEventNotificationEngine' && $enumValue == VidiunEventNotificationTemplateType::EMAIL)
			return 'VDispatchEmailNotificationEngine';
			
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
	public static function getEmailNotificationFileSyncObjectTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('FileSyncObjectType', $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getEmailNotificationTemplateTypeCoreValue($valueName)
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

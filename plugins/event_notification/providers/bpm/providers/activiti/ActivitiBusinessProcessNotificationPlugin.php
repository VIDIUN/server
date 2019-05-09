<?php
/**
 * @package plugins.activitiBusinessProcessNotification
 */
class ActivitiBusinessProcessNotificationPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunPending, IVidiunObjectLoader, IVidiunEnumerator
{
	const PLUGIN_NAME = 'activitiBusinessProcessNotification';
	
	const BPM_NOTIFICATION_PLUGIN_NAME = 'businessProcessNotification';
	const BPM_NOTIFICATION_PLUGIN_VERSION_MAJOR = 1;
	const BPM_NOTIFICATION_PLUGIN_VERSION_MINOR = 0;
	const BPM_NOTIFICATION_PLUGIN_VERSION_BUILD = 0;
	
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
			return array('ActivitiBusinessProcessProvider');
	
		if($baseEnumName == 'BusinessProcessProvider')
			return array('ActivitiBusinessProcessProvider');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
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
		if($baseClass == 'vBusinessProcessProvider')
		{
			if(class_exists('VidiunClient') && defined('VidiunBusinessProcessProvider::ACTIVITI'))
			{
				if($enumValue == VidiunBusinessProcessProvider::ACTIVITI)
					return 'vActivitiBusinessProcessProvider';
			}
			elseif(class_exists('Vidiun_Client_Client') && defined('Vidiun_Client_BusinessProcessNotification_Enum_BusinessProcessProvider::ACTIVITI'))
			{
				if($enumValue == Vidiun_Client_BusinessProcessNotification_Enum_BusinessProcessProvider::ACTIVITI)
					return 'vActivitiBusinessProcessProvider';
			}
			elseif($enumValue == self::getApiValue(ActivitiBusinessProcessProvider::ACTIVITI))
			{
				return 'vActivitiBusinessProcessProvider';
			}
		}
			
		if($baseClass == 'BusinessProcessServer' && $enumValue == self::getActivitiBusinessProcessProviderCoreValue(ActivitiBusinessProcessProvider::ACTIVITI))
			return 'ActivitiBusinessProcessServer';
			
		if($baseClass == 'VidiunBusinessProcessServer' && $enumValue == self::getActivitiBusinessProcessProviderCoreValue(ActivitiBusinessProcessProvider::ACTIVITI))
			return 'VidiunActivitiBusinessProcessServer';
					
		return null;
	}

	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn() 
	{
		$minVersion = new VidiunVersion(
			self::BPM_NOTIFICATION_PLUGIN_VERSION_MAJOR,
			self::BPM_NOTIFICATION_PLUGIN_VERSION_MINOR,
			self::BPM_NOTIFICATION_PLUGIN_VERSION_BUILD
		);
		$dependency = new VidiunDependency(self::BPM_NOTIFICATION_PLUGIN_NAME, $minVersion);
		
		return array($dependency);
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getActivitiBusinessProcessProviderCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BusinessProcessProvider', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}

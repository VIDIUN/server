<?php
/**
 * @package plugins.businessProcessNotification
 */
class BusinessProcessNotificationPlugin extends VidiunPlugin implements IVidiunVersion, IVidiunPending, IVidiunObjectLoader, IVidiunEnumerator, IVidiunServices, IVidiunApplicationPartialView, IVidiunAdminConsolePages, IVidiunEventConsumers, IVidiunApplicationTranslations
{
	const PLUGIN_NAME = 'businessProcessNotification';
	const PLUGIN_VERSION_MAJOR = 1;
	const PLUGIN_VERSION_MINOR = 0;
	const PLUGIN_VERSION_BUILD = 0;
	
	const EVENT_NOTIFICATION_PLUGIN_NAME = 'eventNotification';
	const EVENT_NOTIFICATION_PLUGIN_VERSION_MAJOR = 1;
	const EVENT_NOTIFICATION_PLUGIN_VERSION_MINOR = 0;
	const EVENT_NOTIFICATION_PLUGIN_VERSION_BUILD = 0;
	
	const BUSINESS_PROCESS_NOTIFICATION_FLOW_MANAGER = 'vBusinessProcessNotificationFlowManager';
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunVersion::getVersion()
	 */
	public static function getVersion()
	{
		return new VidiunVersion(
			self::PLUGIN_VERSION_MAJOR,
			self::PLUGIN_VERSION_MINOR,
			self::PLUGIN_VERSION_BUILD
		);		
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(self::BUSINESS_PROCESS_NOTIFICATION_FLOW_MANAGER);
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('BusinessProcessNotificationTemplateType');
	
		if($baseEnumName == 'EventNotificationTemplateType')
			return array('BusinessProcessNotificationTemplateType');
			
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
		if($baseClass == 'VidiunEventNotificationDispatchJobData')
		{
			if(
				$enumValue == self::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_START) || 
				$enumValue == self::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_SIGNAL) || 
				$enumValue == self::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_ABORT)
			)
				return 'VidiunBusinessProcessNotificationDispatchJobData';
		}
		
		if($baseClass == 'EventNotificationTemplate')
		{
			if($enumValue == self::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_START))
				return 'BusinessProcessStartNotificationTemplate';
				
			if($enumValue == self::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_SIGNAL))
				return 'BusinessProcessSignalNotificationTemplate';
				
			if($enumValue == self::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_ABORT))
				return 'BusinessProcessAbortNotificationTemplate';
		}
	
		if($baseClass == 'VidiunEventNotificationTemplate')
		{
			if($enumValue == self::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_START))
				return 'VidiunBusinessProcessStartNotificationTemplate';
				
			if($enumValue == self::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_SIGNAL))
				return 'VidiunBusinessProcessSignalNotificationTemplate';
				
			if($enumValue == self::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_ABORT))
				return 'VidiunBusinessProcessAbortNotificationTemplate';
		}
	
		if($baseClass == 'Form_EventNotificationTemplateConfiguration')
		{
			if(
				$enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::BPM_START || 
				$enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::BPM_SIGNAL || 
				$enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::BPM_ABORT
			)
				return 'Form_BusinessProcessNotificationTemplateConfiguration';
		}
	
		if($baseClass == 'Vidiun_Client_EventNotification_Type_EventNotificationTemplate')
		{
			if($enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::BPM_START)
				return 'Vidiun_Client_BusinessProcessNotification_Type_BusinessProcessStartNotificationTemplate';
				
			if($enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::BPM_SIGNAL)
				return 'Vidiun_Client_BusinessProcessNotification_Type_BusinessProcessSignalNotificationTemplate';
				
			if($enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::BPM_ABORT)
				return 'Vidiun_Client_BusinessProcessNotification_Type_BusinessProcessAbortNotificationTemplate';
		}
	
		if($baseClass == 'VDispatchEventNotificationEngine')
		{
			if(
				$enumValue == VidiunEventNotificationTemplateType::BPM_START ||
				$enumValue == VidiunEventNotificationTemplateType::BPM_SIGNAL ||
				$enumValue == VidiunEventNotificationTemplateType::BPM_ABORT
			)
				return 'VDispatchBusinessProcessNotificationEngine';
		}
			
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
	
	/* (non-PHPdoc)
	 * @see IVidiunApplicationPartialView::getApplicationPartialViews()
	 */
	public static function getApplicationPartialViews($controller, $action)
	{
		if($controller == 'plugin' && $action == 'EventNotificationTemplateConfigureAction')
		{
			return array(
				new Vidiun_View_Helper_BusinessProcessNotificationTemplateConfigure(),
			);
		}
	
		if($controller == 'batch' && $action == 'entryInvestigation')
		{
			return array(
				new Vidiun_View_Helper_EntryBusinessProcess(),
			);
		}
		
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages() 
	{
		return array(
			new BusinessProcessNotificationTemplatesListProcessesAction(),
		);
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getBusinessProcessNotificationTemplateTypeCoreValue($valueName)
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

	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap() 
	{
		return array(
			'businessProcessServer' => 'BusinessProcessServerService',
			'businessProcessCase' => 'BusinessProcessCaseService',
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunApplicationTranslations::getTranslations()
	 */
	public static function getTranslations($locale)
	{
		$array = array();
		
		$langFilePath = __DIR__ . "/config/lang/$locale.php";
		if(!file_exists($langFilePath))
		{
			$default = 'en';
			$langFilePath = __DIR__ . "/config/lang/$default.php";
		}
		
		$array = include($langFilePath);
	
		return array($locale => $array);
	}
}

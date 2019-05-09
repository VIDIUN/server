<?php
/**
 * @package plugins.eventNotification
 */
class EventNotificationPlugin extends VidiunPlugin implements IVidiunVersion, IVidiunPermissions, IVidiunEventConsumers, IVidiunServices, IVidiunAdminConsolePages, IVidiunEnumerator, IVidiunObjectLoader
{
	const PLUGIN_NAME = 'eventNotification';
	const PLUGIN_VERSION_MAJOR = 1;
	const PLUGIN_VERSION_MINOR = 0;
	const PLUGIN_VERSION_BUILD = 0;
	
	const EVENT_NOTIFICATION_FLOW_MANAGER = 'vEventNotificationFlowManager';
	const EVENT_NOTIFICATION_OBJECT_COPIED_HANDLER = 'vEventNotificationObjectCopiedHandler';
	
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
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		return true;
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('EventNotificationBatchType', 'EventNotificationPermissionName', 'EventNotificationConditionType');
	
		if($baseEnumName == 'BatchJobType')
			return array('EventNotificationBatchType');
			
		if($baseEnumName == 'PermissionName')
			return array('EventNotificationPermissionName');
			
		if($baseEnumName == 'ConditionType')
			return array('EventNotificationConditionType');
			
		return array();
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(self::EVENT_NOTIFICATION_FLOW_MANAGER, self::EVENT_NOTIFICATION_OBJECT_COPIED_HANDLER);
	}

	/* (non-PHPdoc)
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages() 
	{
		return array(
			new EventNotificationTemplatesListAction(),
			new EventNotificationTemplateConfigureAction(),
			new EventNotificationTemplateUpdateStatusAction(),
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap() 
	{
		return array(
			'eventNotificationTemplate' => 'EventNotificationTemplateService',
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'VidiunJobData' && $enumValue == self::getApiValue(EventNotificationBatchType::EVENT_NOTIFICATION_HANDLER) && isset($constructorArgs['coreJobSubType']))
			return VidiunPluginManager::loadObject('VidiunEventNotificationDispatchJobData', $constructorArgs['coreJobSubType']);
	
		if($baseClass == 'VidiunCondition')
		{
			if($enumValue == EventNotificationPlugin::getConditionTypeCoreValue(EventNotificationConditionType::EVENT_NOTIFICATION_FIELD))
				return new VidiunEventFieldCondition();
				
			if($enumValue == EventNotificationPlugin::getConditionTypeCoreValue(EventNotificationConditionType::EVENT_NOTIFICATION_OBJECT_CHANGED))
				return new VidiunEventObjectChangedCondition();
		}
		
		return null;
	}
		
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'VidiunJobData' && $enumValue == self::getApiValue(EventNotificationBatchType::EVENT_NOTIFICATION_HANDLER))
			return 'VidiunEventNotificationDispatchJobData';
			
		if($baseClass == 'EventNotificationEventObjectType')
		{
			switch($enumValue)
			{
			    case EventNotificationEventObjectType::ENTRY:
			    	return 'entry';
			    	
			    case EventNotificationEventObjectType::CATEGORY:
					return 'category';

			    case EventNotificationEventObjectType::ASSET:
					return 'asset';

			    case EventNotificationEventObjectType::FLAVORASSET:
					return 'flavorAsset';

			    case EventNotificationEventObjectType::THUMBASSET:
					return 'thumbAsset';

			    case EventNotificationEventObjectType::VUSER:
					return 'vuser';

			    case EventNotificationEventObjectType::ACCESSCONTROL:
					return 'accessControl';

				case EventNotificationEventObjectType::BATCHJOB:
					return 'BatchJob';

				case EventNotificationEventObjectType::BULKUPLOADRESULT:
					return 'BulkUploadResult';

				case EventNotificationEventObjectType::CATEGORYVUSER:
					return 'categoryVuser';

				case EventNotificationEventObjectType::CONVERSIONPROFILE2:
					return 'conversionProfile2';

				case EventNotificationEventObjectType::FLAVORPARAMS:
					return 'flavorParams';

				case EventNotificationEventObjectType::FLAVORPARAMSCONVERSIONPROFILE:
					return 'flavorParamsConversionProfile';

				case EventNotificationEventObjectType::FLAVORPARAMSOUTPUT:
					return 'flavorParamsOutput';

				case EventNotificationEventObjectType::GENERICSYNDICATIONFEED:
					return 'genericSyndicationFeed';

				case EventNotificationEventObjectType::VUSERTOUSERROLE:
					return 'VuserToUserRole';

				case EventNotificationEventObjectType::PARTNER:
					return 'Partner';

				case EventNotificationEventObjectType::PERMISSION:
					return 'Permission';

				case EventNotificationEventObjectType::PERMISSIONITEM:
					return 'PermissionItem';

				case EventNotificationEventObjectType::PERMISSIONTOPERMISSIONITEM:
					return 'PermissionToPermissionItem';

				case EventNotificationEventObjectType::SCHEDULER:
					return 'Scheduler';

				case EventNotificationEventObjectType::SCHEDULERCONFIG:
					return 'SchedulerConfig';

				case EventNotificationEventObjectType::SCHEDULERSTATUS:
					return 'SchedulerStatus';

				case EventNotificationEventObjectType::SCHEDULERWORKER:
					return 'SchedulerWorker';

				case EventNotificationEventObjectType::STORAGEPROFILE:
					return 'StorageProfile';

				case EventNotificationEventObjectType::SYNDICATIONFEED:
					return 'syndicationFeed';

				case EventNotificationEventObjectType::THUMBPARAMS:
					return 'thumbParams';

				case EventNotificationEventObjectType::THUMBPARAMSOUTPUT:
					return 'thumbParamsOutput';

				case EventNotificationEventObjectType::UPLOADTOKEN:
					return 'UploadToken';

				case EventNotificationEventObjectType::USERLOGINDATA:
					return 'UserLoginData';

				case EventNotificationEventObjectType::USERROLE:
					return 'UserRole';

				case EventNotificationEventObjectType::WIDGET:
					return 'widget';

				case EventNotificationEventObjectType::CATEGORYENTRY:
					return 'categoryEntry';

				case EventNotificationEventObjectType::LIVE_STREAM:
					return 'LiveStreamEntry';

				case EventNotificationEventObjectType::ENTRY_SERVER_NODE:
					return 'EntryServerNode';

				case EventNotificationEventObjectType::SERVER_NODE:
					return 'ServerNode';
				
				case EventNotificationEventObjectType::ENTRY_VENDOR_TASK:
					return 'EntryVendorTask';
				
				case EventNotificationEventObjectType::REACH_PROFILE:
					return 'ReachProfile';

			}
		}
		
		return null;
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getBatchJobTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BatchJobType', $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getConditionTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('ConditionType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}

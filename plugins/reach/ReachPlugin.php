<?php

/**
 * Enable time based cue point objects management on entry objects
 * @package plugins.reach
 */
class ReachPlugin extends VidiunPlugin implements IVidiunServices, IVidiunPermissions, IVidiunVersion, IVidiunAdminConsolePages, IVidiunPending, IVidiunEventConsumers, IVidiunEnumerator, IVidiunObjectLoader, IVidiunSearchDataContributor
{
	const PLUGIN_NAME = 'reach';
	const PLUGIN_VERSION_MAJOR = 1;
	const PLUGIN_VERSION_MINOR = 0;
	const PLUGIN_VERSION_BUILD = 0;
	const REACH_MANAGER = 'vReachManager';
	const REACH_FLOW_MANAGER = 'vReachFlowManager';
	const SEARCH_FIELD_CATALOG_ITEM_DATA = 'cid';
	const SEARCH_TEXT_SUFFIX = 'ciend';
	const CATALOG_ITEM_INDEX_PREFIX = 'cis_';
	const CATALOG_ITEM_INDEX_SUFFIX = 'cie_';
	const CATALOG_ITEM_INDEX_SERVICE_TYPE = 'cist';
	const CATALOG_ITEM_INDEX_TURN_AROUND_TIME = 'citat';
	const CATALOG_ITEM_INDEX_SERVICE_FEATURE = 'cisf';
	const CATALOG_ITEM_INDEX_LANGUAGE = 'cil';
	const CATALOG_ITEM_INDEX_TARGET_LANGUAGE = 'citl';
	
	/**
	 * return field name as appears in index schema
	 * @param string $fieldName
	 */
	public static function getSearchFieldName($fieldName)
	{
		if ($fieldName == self::SEARCH_FIELD_CATALOG_ITEM_DATA)
			return 'catalog_item_data';
		
		return CuePointPlugin::getPluginName() . '_' . $fieldName;
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if ($baseClass == 'VidiunCondition' && $enumValue == ReachPlugin::getConditionTypeCoreValue(ReachConditionType::EVENT_CATEGORY_ENTRY))
			return 'VidiunCategoryEntryCondition';
		
		if ($baseClass == 'vRuleAction' && $enumValue == ReachPlugin::getRuleActionTypeCoreValue(ReachRuleActionType::ADD_ENTRY_VENDOR_TASK))
			return 'vAddEntryVendorTaskAction';
		
		if ($baseClass == 'VidiunRuleAction' && ReachPlugin::getRuleActionTypeCoreValue(ReachRuleActionType::ADD_ENTRY_VENDOR_TASK))
			return 'VidiunAddEntryVendorTaskAction';
		
		if ($baseClass == 'vJobData')
		{
			if ($enumValue == self::getBatchJobTypeCoreValue(ReachEntryVendorTasksCsvBatchType::ENTRY_VENDOR_TASK_CSV))
			{
				return 'vEntryVendorTaskCsvJobData';
			}
		}
		
		if ($baseClass == 'VidiunJobData')
		{
			if ($enumValue == self::getApiValue(ReachEntryVendorTasksCsvBatchType::ENTRY_VENDOR_TASK_CSV))
			{
				return 'VidiunEntryVendorTaskCsvJobData';
			}
		}
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if (is_null($baseEnumName))
			return array('SyncReachCreditTaskBatchType', 'ReachConditionType', 'ReachEntryVendorTasksCsvBatchType', 'ReachRuleActionType', 'EntryVendorTaskExportObjectType');
		
		if ($baseEnumName == 'BatchJobType')
			return array('SyncReachCreditTaskBatchType', 'ReachEntryVendorTasksCsvBatchType');
		
		if ($baseEnumName == 'ConditionType')
			return array('ReachConditionType');
		
		if ($baseEnumName == 'RuleActionType')
			return array('ReachRuleActionType');
		
		if ($baseEnumName == 'ExportObjectType')
			return array('EntryVendorTaskExportObjectType');
		
		return array();
	}
	
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
		if (in_array($partnerId, array(Partner::ADMIN_CONSOLE_PARTNER_ID, Partner::BATCH_PARTNER_ID, PartnerPeer::GLOBAL_PARTNER)))
			return true;
		
		if (PermissionPeer::isValidForPartner(PermissionName::REACH_VENDOR_PARTNER_PERMISSION, $partnerId))
			return true;
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(self::PLUGIN_NAME);
	}
	
	
	public static function isAllowAdminApi($actionApi = null)
	{
		$currentPermissions = Infra_AclHelper::getCurrentPermissions();
		return ($currentPermissions && in_array(Vidiun_Client_Enum_PermissionName::SYSTEM_ADMIN_CATALOG_ITEM_MODIFY, $currentPermissions));
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap()
	{
		$map = array(
			'vendorCatalogItem' => 'VendorCatalogItemService',
			'reachProfile' => 'ReachProfileService',
			'entryVendorTask' => 'EntryVendorTaskService',
			'partnerCatalogItem' => 'PartnerCatalogItemService',
		);
		return $map;
	}
	
	/*
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages()
	{
		$pages = array();
		$pages[] = new CatalogItemListAction();
		$pages[] = new CatalogItemConfigureAction();
		$pages[] = new CatalogItemSetStatusAction();
		$pages[] = new PartnerCatalogItemListAction();
		$pages[] = new PartnerCatalogItemConfigureAction();
		$pages[] = new PartnerCatalogItemSetStatusAction();
		$pages[] = new PartnerCatalogItemsCloneAction();
		$pages[] = new ReachProfileListAction();
		$pages[] = new ReachProfileConfigureAction();
		$pages[] = new ReachProfileSetStatusAction();
		$pages[] = new ReachProfileCreditConfigureAction();
		$pages[] = new ReachProfileCloneAction();

		return $pages;
	}
	
	/* (non-PHPdoc)
 	 * @see IVidiunPending::dependsOn()
 	*/
	public static function dependsOn()
	{
		$eventNotificationDependency = new VidiunDependency(EventNotificationPlugin::getPluginName());
		return array($eventNotificationDependency);
	}
	
	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::REACH_FLOW_MANAGER,
			self::REACH_MANAGER
		);
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
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getRuleActionTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('RuleActionType', $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getExportTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('ExportObjectType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if ($baseClass == 'VidiunCondition' && $enumValue == ReachPlugin::getConditionTypeCoreValue(ReachConditionType::EVENT_CATEGORY_ENTRY))
			return new VidiunCategoryEntryCondition();
		
		if ($baseClass == 'vRuleAction' && $enumValue == ReachPlugin::getRuleActionTypeCoreValue(ReachRuleActionType::ADD_ENTRY_VENDOR_TASK))
			return new vAddEntryVendroTaskAction();
		
		if ($baseClass == 'VidiunRuleAction' && $enumValue ==  ReachPlugin::getRuleActionTypeCoreValue(ReachRuleActionType::ADD_ENTRY_VENDOR_TASK))
			return new VidiunAddEntryVendorTaskAction();
		
		if ($baseClass == 'vJobData')
		{
			if ($enumValue == self::getBatchJobTypeCoreValue(ReachEntryVendorTasksCsvBatchType::ENTRY_VENDOR_TASK_CSV))
			{
				return new vEntryVendorTaskCsvJobData();
			}
		}
		
		if ($baseClass == 'VidiunJobData')
		{
			if ($enumValue == self::getApiValue(ReachEntryVendorTasksCsvBatchType::ENTRY_VENDOR_TASK_CSV) ||
				$enumValue == self::getBatchJobTypeCoreValue(ReachEntryVendorTasksCsvBatchType::ENTRY_VENDOR_TASK_CSV)
			)
			{
				return new VidiunEntryVendorTaskCsvJobData();
			}
		}
		
		if($baseClass == 'VidiunJobData' && $enumValue == BatchJobType::EXPORT_CSV && (isset($constructorArgs['coreJobSubType']) &&  $constructorArgs['coreJobSubType']== self::getExportTypeCoreValue(EntryVendorTaskExportObjectType::ENTRY_VENDOR_TASK)))
		{
			return new VidiunEntryVendorTaskCsvJobData();
		}
		
		if ($baseClass == 'VObjectExportEngine' && $enumValue == VidiunExportObjectType::ENTRY_VENDOR_TASK)
		{
			return new VExportEntryVendorTaskEngine($constructorArgs);
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
	
	/* (non-PHPdoc)
	 * @see IVidiunSearchDataContributor::getSearchData()
	 */
	public static function getSearchData(BaseObject $object)
	{
		if ($object instanceof EntryVendorTask && self::isAllowedPartner($object->getPartnerId()))
			return self::getEntryVendorTaskSearchData($object);
		
		return null;
	}
	
	public static function getEntryVendorTaskSearchData(EntryVendorTask $entryVendorTask)
	{
		$catalogItem = $entryVendorTask->getCatalogItem();
		$catalogItemSearchField = ReachPlugin::getSearchFieldName(ReachPlugin::SEARCH_FIELD_CATALOG_ITEM_DATA);
		
		$contributedData = self::buildDataOnTask($catalogItem, $entryVendorTask->getPartnerId());
		
		$searchValues = array(
			$catalogItemSearchField => ReachPlugin::PLUGIN_NAME . "_" . $entryVendorTask->getPartnerId() . ' ' . $contributedData . ' ' . ReachPlugin::SEARCH_TEXT_SUFFIX
		);
		
		return $searchValues;
	}
	
	public static function buildDataOnTask(VendorCatalogItem $catalogItem, $partnerId)
	{
		$data = self::CATALOG_ITEM_INDEX_PREFIX . $partnerId;
		
		$data .= " " . self::CATALOG_ITEM_INDEX_SERVICE_TYPE . $catalogItem->getServiceType();
		$data .= " " . self::CATALOG_ITEM_INDEX_SERVICE_FEATURE . $catalogItem->getServiceFeature();
		$data .= " " . self::CATALOG_ITEM_INDEX_TURN_AROUND_TIME . $catalogItem->getTurnAroundTime();
		$data .= " " . self::CATALOG_ITEM_INDEX_LANGUAGE . $catalogItem->getSourceLanguage();
		if($catalogItem->getTargetLanguage())
			$data .= " " . self::CATALOG_ITEM_INDEX_TARGET_LANGUAGE . $catalogItem->getTargetLanguage();
		
		$data .= " " . self::CATALOG_ITEM_INDEX_SUFFIX . $partnerId;
		
		return $data;
	}
	
}

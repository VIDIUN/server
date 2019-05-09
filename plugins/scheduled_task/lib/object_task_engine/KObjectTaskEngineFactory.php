<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskEngineFactory
{
	public static function getInstanceByType($type)
	{
		switch($type)
		{
			case ObjectTaskType::DELETE_ENTRY:
				return new VObjectTaskDeleteEntryEngine();
			case ObjectTaskType::MODIFY_CATEGORIES:
				return new VObjectTaskModifyCategoriesEngine();
			case ObjectTaskType::DELETE_ENTRY_FLAVORS:
				return new VObjectTaskDeleteEntryFlavorsEngine();
			case ObjectTaskType::CONVERT_ENTRY_FLAVORS:
				return new VObjectTaskConvertEntryFlavorsEngine();
			case ObjectTaskType::DELETE_LOCAL_CONTENT:
				return new VObjectTaskDeleteLocalContentEngine();
			case ObjectTaskType::STORAGE_EXPORT:
				return new VObjectTaskStorageExportEngine();
			case ObjectTaskType::MODIFY_ENTRY:
				return new VObjectTaskModifyEntryEngine();
			default:
				return VidiunPluginManager::loadObject('VObjectTaskEntryEngineBase', $type);
		}
	}
} 
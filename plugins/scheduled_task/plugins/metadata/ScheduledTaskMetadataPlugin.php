<?php
/**
 * Extension plugin for scheduled task plugin to add support metadata related object tasks
 *
 * @package plugins.scheduledTaskMetadata
 */
class ScheduledTaskMetadataPlugin extends VidiunPlugin implements IVidiunPending, IVidiunEnumerator, IVidiunObjectLoader
{
	const PLUGIN_NAME = 'scheduledTaskMetadata';
	
	const SCHEDULED_TASK_PLUGIN_NAME = 'scheduledTask';
	
	const METADATA_PLUGIN_NAME = 'metadata';
	const METADATA_PLUGIN_VERSION_MAJOR = 1;
	const METADATA_PLUGIN_VERSION_MINOR = 0;
	const METADATA_PLUGIN_VERSION_BUILD = 0;
	
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
		$metadataVersion = new VidiunVersion(self::METADATA_PLUGIN_VERSION_MAJOR, self::METADATA_PLUGIN_VERSION_MINOR, self::METADATA_PLUGIN_VERSION_BUILD);
		
		$scheduledTaskDependency = new VidiunDependency(self::SCHEDULED_TASK_PLUGIN_NAME);
		$metadataDependency = new VidiunDependency(self::METADATA_PLUGIN_NAME, $metadataVersion);
		
		return array($scheduledTaskDependency, $metadataDependency);
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('ExecuteMetadataXsltObjectTaskType');
	
		if($baseEnumName == 'ObjectTaskType')
			return array('ExecuteMetadataXsltObjectTaskType');
			
		return array();
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if (class_exists('Vidiun_Client_Client'))
			return null;

		if (class_exists('VidiunClient'))
		{
			if ($baseClass == 'VObjectTaskEntryEngineBase' && $enumValue == VidiunObjectTaskType::EXECUTE_METADATA_XSLT)
				return new VObjectTaskExecuteMetadataXsltEngine();
		}
		else
		{
			$apiValue = self::getApiValue(ExecuteMetadataXsltObjectTaskType::EXECUTE_METADATA_XSLT);
			$executeMetadataXsltObjectTaskCoreValue = vPluginableEnumsManager::apiToCore('ObjectTaskType', $apiValue);
			if($baseClass == 'VidiunObjectTask' && $enumValue == $executeMetadataXsltObjectTaskCoreValue)
				return new VidiunExecuteMetadataXsltObjectTask();

			if ($baseClass == 'VObjectTaskEntryEngineBase' && $enumValue == $apiValue)
				return new VObjectTaskExecuteMetadataXsltEngine();
		}

		return null;
	}
		
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		return null;
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}

	/* (non-PHPdoc)
 * @see IVidiunConfigurator::getConfig()
 */
	public static function getConfig($configName)
	{
		if($configName == 'generator')
			return new Zend_Config_Ini(dirname(__FILE__) . '/config/generator.ini');

		return null;
	}
}

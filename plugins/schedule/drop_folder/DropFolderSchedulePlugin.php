<?php
/**
 * @package plugins.scheduleDropFolder
 */
class DropFolderSchedulePlugin extends VidiunPlugin implements IVidiunEnumerator, IVidiunObjectLoader, IVidiunEventConsumers, IVidiunBulkUpload, IVidiunPending
{
	const PLUGIN_NAME = 'scheduleDropFolder';
	const DROP_FOLDER_EVENTS_CONSUMER = 'vDropFolderICalEventsConsumer';
	
	/**
	 * Returns the plugin name
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$bulkUploadDependency = new VidiunDependency(BulkUploadSchedulePlugin::PLUGIN_NAME);
		$dropFolderDependency = new VidiunDependency(DropFolderPlugin::PLUGIN_NAME);
		
		return array($bulkUploadDependency, $dropFolderDependency);
	}
	
	/**
	 *
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('DropFolderFileHandlerScheduleType', 'DropFolderScheduleType');

		if($baseEnumName == 'DropFolderFileHandlerType')
			return array('DropFolderFileHandlerScheduleType');

		if($baseEnumName == 'BulkUploadType')
			return array('DropFolderScheduleType');
		
		return array();
	}

	/**
	 * {@inheritDoc}
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'Vidiun_Client_DropFolder_Type_DropFolderFileHandlerConfig' && $enumValue == Vidiun_Client_DropFolder_Enum_DropFolderFileHandlerType::ICAL)
		{
			return new Vidiun_Client_ScheduleDropFolder_Type_DropFolderICalBulkUploadFileHandlerConfig();
		}
		
		if($baseClass == 'Form_BaseFileHandlerConfig' && $enumValue == Vidiun_Client_DropFolder_Enum_DropFolderFileHandlerType::ICAL)
		{
			return new Form_ICalFileHandlerConfig();
		}
		
		if($baseClass == 'VidiunDropFolderFileHandlerConfig' && $enumValue == self::getFileHandlerTypeCoreValue(DropFolderFileHandlerScheduleType::ICAL))
		{
			return new VidiunDropFolderICalBulkUploadFileHandlerConfig();
		}

		if($baseClass == 'vBulkUploadJobData' && $enumValue == self::getBulkUploadTypeCoreValue(DropFolderScheduleType::DROP_FOLDER_ICAL))
		{
			return new vBulkUploadICalJobData();
		}
		
		if($baseClass == 'VidiunBulkUploadJobData' && $enumValue == self::getBulkUploadTypeCoreValue(DropFolderScheduleType::DROP_FOLDER_ICAL))
		{
			return new VidiunBulkUploadICalJobData();
		}
				
		if($baseClass == 'VBulkUploadEngine' && class_exists('VidiunClient'))
		{	
			list($job) = $constructorArgs;
			if($enumValue == VidiunBulkUploadType::DROP_FOLDER_ICAL)
			{
				return new BulkUploadEngineDropFolderICal($job);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::DROP_FOLDER_EVENTS_CONSUMER,
		);
	}
	
	/**
	 * Returns the correct file extension for bulk upload type
	 *
	 * @param int $enumValue code API value
	 */
	public static function getFileExtension($enumValue)
	{
		if($enumValue == self::getBulkUploadTypeCoreValue(DropFolderScheduleType::DROP_FOLDER_ICAL))
			return 'ics';
	}
	
	/**
	 * Returns the log file for bulk upload job
	 *
	 * @param BatchJob $batchJob bulk upload batchjob
	 */
	public static function writeBulkUploadLogFile($batchJob)
	{
		if($batchJob->getJobSubType() != self::getBulkUploadTypeCoreValue(DropFolderScheduleType::DROP_FOLDER_ICAL))
		{
			return;
		}
		
		BulkUploadSchedulePlugin::writeICalBulkUploadLogFile($batchJob);
	}
	
	/**
	 *
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getFileHandlerTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('DropFolderFileHandlerType', $value);
	}
	
	/**
	 *
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getBulkUploadTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BulkUploadType', $value);
	}
	
	/**
	 *
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}

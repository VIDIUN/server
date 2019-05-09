<?php
/**
 * @package plugins.dropFolder
 */
class DropFolderPlugin extends VidiunPlugin implements IVidiunPending, IVidiunServices, IVidiunPermissions, IVidiunObjectLoader, IVidiunEnumerator, IVidiunAdminConsolePages, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'dropFolder';
	const DROP_FOLDER_EVENTS_CONSUMER = 'vDropFolderEventsConsumer';
	const METADATA_PLUGIN_NAME = 'metadata';
	
	//Error Messages
	const ERROR_CONNECT_MESSAGE = 'Failed to connect to the drop folder. Please verify host and port information and/or actual access to the drop folder';
	const ERROR_AUTENTICATE_MESSAGE = 'Failed to authenticate drop folder credentials or keys. Please verify credential settings';
	const ERROR_GET_PHISICAL_FILE_LIST_MESSAGE = 'Failed to list files located in the  drop folder. Please verify drop folder path and/or listing permissions in physical drop folder path';
	const ERROR_GET_DB_FILE_LIST_MESSAGE = 'Failed to list drop folder records in Vidiun DB.  Please verify that Vidiun\'s services and batch system is running properly';
	const DROP_FOLDER_APP_ERROR_MESSAGE = 'Drop folder applicative error. Please verify that Vidiun\'s services and batch system is running properly. Log Description: ';
	const ERROR_READING_FILE_MESSAGE = 'Failed to read file or file details at: ';
	const ERROR_DELETING_FILE_MESSAGE = 'Failed to delete the file at: ';
	const ERROR_UPDATE_FILE_MESSAGE = 'Failed to update the drop folder file record in Vidiun.';
	const SLUG_REGEX_NO_MATCH_MESSAGE = 'Failed to parse filename according to drop folder naming convention definition';
	const ERROR_ADDING_CONTENT_PROCESSOR_MESSAGE = 'Failed to activate the drop folder engine processing for this file';
	const ERROR_IN_CONTENT_PROCESSOR_MESSAGE = 'Drop folder engine processing failure';
	const ERROR_DOWNLOADING_FILE_MESSAGE = 'Failed in file transferring from the drop folder to Vidiun';
	const FLAVOR_NOT_FOUND_MESSAGE = 'Failed to handle file. Could not find a matched transcoding flavor setting with system name: ';
	
	public static function dependsOn()
	{
		$metadataDependency = new VidiunDependency(self::METADATA_PLUGIN_NAME);
		
		return array($metadataDependency);
	}
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public static function isAllowedPartner($partnerId)
	{
		if (in_array($partnerId, array(Partner::ADMIN_CONSOLE_PARTNER_ID, Partner::BATCH_PARTNER_ID)))
			return true;
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(self::PLUGIN_NAME);		
	}
	
	/**
	 * @return array<string,string> in the form array[serviceName] = serviceClass
	 */
	public static function getServicesMap()
	{
		$map = array(
			'dropFolder' => 'DropFolderService',
			'dropFolderFile' => 'DropFolderFileService',
		);
		return $map;
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @param array $constructorArgs
	 * @return object
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{			
		$objectClass = self::getObjectClass($baseClass, $enumValue);
		
		if (is_null($objectClass))
		 {
			if ($baseClass == 'VidiunJobData')
			{
				$jobSubType = $constructorArgs["coreJobSubType"];
			    if ($enumValue == DropFolderPlugin::getApiValue(DropFolderBatchType::DROP_FOLDER_CONTENT_PROCESSOR) &&
					in_array($jobSubType, array(DropFolderType::FTP, DropFolderType::LOCAL, DropFolderType::S3, DropFolderType::SCP, DropFolderType::SFTP)))
				{
					return new VidiunDropFolderContentProcessorJobData();
				}
			}
			return null;
		}
		
		if (!is_null($constructorArgs) && $objectClass != 'VidiunDropFolderContentProcessorJobData')
		{
			$reflect = new ReflectionClass($objectClass);
			return $reflect->newInstanceArgs($constructorArgs);
		}
		else
		{
			return new $objectClass();
		}
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @return string
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{	
		if ($baseClass == 'DropFolder')
		{
		    if ($enumValue == DropFolderType::LOCAL)
			{
				return 'DropFolder';
			}
			if ($enumValue == DropFolderType::FTP)
			{
				return 'FtpDropFolder';
			}
			if ($enumValue == DropFolderType::SCP)
			{
				return 'ScpDropFolder';
			}
			if ($enumValue == DropFolderType::SFTP)
			{
				return 'SftpDropFolder';
			}
		}
		
		if (class_exists('Vidiun_Client_Client'))
		{
			if ($baseClass == 'Vidiun_Client_DropFolder_Type_DropFolder')
    		{
    		    if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::LOCAL)
    			{
    				return 'Vidiun_Client_DropFolder_Type_DropFolder';
    			}    		    
    		    if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::FTP)
    			{
    				return 'Vidiun_Client_DropFolder_Type_FtpDropFolder';
    			}
    			if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::SCP)
    			{
    				return 'Vidiun_Client_DropFolder_Type_ScpDropFolder';
    			}
    			if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::SFTP)
    			{
    				return 'Vidiun_Client_DropFolder_Type_SftpDropFolder';
    			}
    		}
    		
    		if ($baseClass == 'Form_DropFolderConfigureExtend_SubForm')
    		{
    		    if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::FTP)
    			{
    				return 'Form_FtpDropFolderConfigureExtend_SubForm';
    			}
    			if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::SCP)
    			{
    				return 'Form_ScpDropFolderConfigureExtend_SubForm';
    			}
    			if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::SFTP)
    			{
    				return 'Form_SftpDropFolderConfigureExtend_SubForm';
    			}
    		}	
		}
		
		if ($baseClass == 'VidiunDropFolderFileHandlerConfig')
		{
			if ($enumValue == VidiunDropFolderFileHandlerType::CONTENT)
			{
				return 'VidiunDropFolderContentFileHandlerConfig';
			}
		}

		if ($baseClass == 'VidiunDropFolder')
		{
		    if ($enumValue == VidiunDropFolderType::LOCAL)
			{
				return 'VidiunDropFolder';
			}
		    if ($enumValue == VidiunDropFolderType::FTP)
			{
				return 'VidiunFtpDropFolder';
			}
			if ($enumValue == VidiunDropFolderType::SCP)
			{
				return 'VidiunScpDropFolder';
			}
			if ($enumValue == VidiunDropFolderType::SFTP)
			{
				return 'VidiunSftpDropFolder';
			}
		}
		
		if ($baseClass == 'VidiunImportJobData')
		{
		    if ($enumValue == 'vDropFolderImportJobData')
			{
				return 'VidiunDropFolderImportJobData';
			}
		}
		
		return null;
	}
	
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('DropFolderBatchType','DropFolderPermissionName', 'DropFolderBatchJobObjectType');
			
		if($baseEnumName == 'BatchJobType')
			return array('DropFolderBatchType');
			
		if($baseEnumName == 'PermissionName')
			return array('DropFolderPermissionName');
			
		if($baseEnumName == 'BatchJobObjectType')
			return array('DropFolderBatchJobObjectType');
			
			
		return array();
	}

	/* (non-PHPdoc)
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages()
	{
		$pages = array();
		$pages[] = new DropFolderListAction();
		$pages[] = new DropFolderConfigureAction();
		$pages[] = new DropFolderSetStatusAction();
		return $pages;
	}
	
	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::DROP_FOLDER_EVENTS_CONSUMER,
		);
	}
	
	public static function getBatchJobObjectTypeCoreValue($valueName)
	{
		return self::getCoreValue('BatchJobObjectType', $valueName);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getCoreValue($type, $valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore($type, $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
}

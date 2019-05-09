<?php
/**
 * @package plugins.FeedDropFolder
 */
class FeedDropFolderPlugin extends VidiunPlugin implements IVidiunPlugin, IVidiunPending, IVidiunObjectLoader, IVidiunEnumerator, IVidiunApplicationTranslations
{
	const PLUGIN_NAME = 'FeedDropFolder';
	const DROP_FOLDER_PLUGIN_NAME = 'dropFolder';
	
	const ERROR_MESSAGE_INCOMPLETE_HANDLING = "Feed is too long- because of handling limitation not all feed items will be handled. Feed Drop Folder ID ";
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName() {
		return self::PLUGIN_NAME;
	}

	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		switch ($baseClass)
		{
			case 'VDropFolderEngine':
				if ($enumValue == VidiunDropFolderType::FEED)
				{
					return new VFeedDropFolderEngine();
				}
				break;
			case ('VidiunDropFolderFile'):
				if ($enumValue == self::getDropFolderTypeCoreValue(FeedDropFolderType::FEED) )
				{
					return new VidiunFeedDropFolderFile();
				}
				break;
			case ('VidiunDropFolder'):
				if ($enumValue == self::getDropFolderTypeCoreValue(FeedDropFolderType::FEED) )
				{
					return new VidiunFeedDropFolder();
				}
				break;
			case 'vDropFolderXmlFileHandler':
				if ($enumValue == self::getDropFolderTypeCoreValue(FeedDropFolderType::FEED))
				{
					return new vDropFolderFeedXmlFileHandler();
				}
				break;
			case 'Form_DropFolderConfigureExtend_SubForm':
				if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::FEED)
				{
					return new Form_FeedDropFolderConfigureExtend_SubForm();
				}
				break;
			case 'Vidiun_Client_DropFolder_Type_DropFolder':
				if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::FEED)
				{
					return new Vidiun_Client_FeedDropFolder_Type_FeedDropFolder();
				}
				break;
		}
	}
	
	public static function getObjectClass($baseClass, $enumValue)
	{
		switch ($baseClass) {
			case 'DropFolderFile':
				if ($enumValue == self::getDropFolderTypeCoreValue(FeedDropFolderType::FEED))
				return 'FeedDropFolderFile';				
				break;
				
			case 'DropFolder':
				if ($enumValue == self::getDropFolderTypeCoreValue(FeedDropFolderType::FEED))
				return 'FeedDropFolder';				
				break;

		}
	}

	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if (!$baseEnumName)
		{
			return array('FeedDropFolderType');
		}
		if ($baseEnumName == 'DropFolderType')
		{
			return array('FeedDropFolderType');
		}
		
		return array();
	}
		
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn() {
		$dropFolderDependency = new VidiunDependency(self::DROP_FOLDER_PLUGIN_NAME);
		
		return array($dropFolderDependency);
	}

	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}

	public static function getDropFolderTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('DropFolderType', $value);
	}

	/**
	 * @return array
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
		
		VidiunLog::info("Loading file [$langFilePath]");
		$array = include($langFilePath);
	
		return array($locale => $array);
	}
}

<?php
/**
 * @package plugins.WebexDropFolder
 */
class WebexDropFolderPlugin extends VidiunPlugin implements IVidiunPending, IVidiunPermissions, IVidiunObjectLoader, IVidiunEnumerator
{
	const PLUGIN_NAME = 'WebexDropFolder';
	const DROP_FOLDER_PLUGIN_NAME = 'dropFolder';
		
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public static function dependsOn()
	{
		$dropFolderDependency = new VidiunDependency(self::DROP_FOLDER_PLUGIN_NAME);
		
		return array($dropFolderDependency);
	}
	
	public static function isAllowedPartner($partnerId)
	{
		if (in_array($partnerId, array(Partner::ADMIN_CONSOLE_PARTNER_ID, Partner::BATCH_PARTNER_ID)))
			return true;
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(self::PLUGIN_NAME);
	}
	
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		switch ($baseClass)
		{
			case 'VDropFolderEngine':
				if ($enumValue == VidiunDropFolderType::WEBEX)
				{
					return new VWebexDropFolderEngine();
				}
				break;
			case ('VidiunDropFolder'):
				if ($enumValue == self::getDropFolderTypeCoreValue(WebexDropFolderType::WEBEX) )
				{
					return new VidiunWebexDropFolder();
				}
				break;
			case ('VidiunDropFolderFile'):
				if ($enumValue == self::getDropFolderTypeCoreValue(WebexDropFolderType::WEBEX) )
				{
					return new VidiunWebexDropFolderFile();
				}
				break;
			case 'vDropFolderContentProcessorJobData':
				if ($enumValue == self::getDropFolderTypeCoreValue(WebexDropFolderType::WEBEX))
				{
					return new vWebexDropFolderContentProcessorJobData();
				}
				break;
			case 'VidiunJobData':
				$jobSubType = $constructorArgs["coreJobSubType"];
			    if ($enumValue == DropFolderPlugin::getApiValue(DropFolderBatchType::DROP_FOLDER_CONTENT_PROCESSOR) &&
					$jobSubType == self::getDropFolderTypeCoreValue(WebexDropFolderType::WEBEX) )
				{
					return new VidiunWebexDropFolderContentProcessorJobData();
				}
				break;
			case 'Form_DropFolderConfigureExtend_SubForm':
				if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::WEBEX)
				{
					return new Form_WebexDropFolderConfigureExtend_SubForm();
				}
				break;
			case 'Vidiun_Client_DropFolder_Type_DropFolder':
				if ($enumValue == Vidiun_Client_DropFolder_Enum_DropFolderType::WEBEX)
				{
					return new Vidiun_Client_WebexDropFolder_Type_WebexDropFolder();
				}
				break;
				break;
				
		}
	}
	
	public static function getObjectClass($baseClass, $enumValue)
	{
		switch ($baseClass) {
			case 'DropFolder':
				if ($enumValue == self::getDropFolderTypeCoreValue(WebexDropFolderType::WEBEX))
				return 'WebexDropFolder';				
				break;
			case 'DropFolderFile':
				if ($enumValue == self::getDropFolderTypeCoreValue(WebexDropFolderType::WEBEX))
				return 'WebexDropFolderFile';				
				break;

		}
	}
	
	public static function getEnums($baseEnumName = null)
	{
		if (!$baseEnumName)
		{
			return array('WebexDropFolderType');
		}
		if ($baseEnumName == 'DropFolderType')
		{
			return array('WebexDropFolderType');
		}

		return array();
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
}

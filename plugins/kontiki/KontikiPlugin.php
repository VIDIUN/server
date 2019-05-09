<?php
/**
 * Enable upload and playback of content to and from Kontiki ECDN
 * @package plugins.kontiki
 */
class KontikiPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunObjectLoader , IVidiunEventConsumers, IVidiunContextDataHelper
{
	const PLUGIN_NAME = 'kontiki';
    
    const KONTIKI_ASSET_TAG = 'kontiki';
	
	const SERVICE_TOKEN_PREFIX = 'srv-';
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getDeliveryProfileType($valueName)
	{
		$apiValue = self::getApiValue($valueName);
		return vPluginableEnumsManager::apiToCore('DeliveryProfileType', $apiValue);
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if ($baseClass == 'VExportEngine')
		{
			if ($enumValue == VidiunStorageProfileProtocol::KONTIKI)
			{
				list($data, $partnerId) = $constructorArgs;
				return new VKontikiExportEngine($data, $partnerId);
			}
		}
		if ($baseClass == 'vStorageExportJobData')
		{
            if ($enumValue == self::getStorageProfileProtocolCoreValue(KontikiStorageProfileProtocol::KONTIKI))
            {
                return new vKontikiStorageExportJobData();
            }
		}
        if ($baseClass == 'vStorageDeleteJobData')
        {
            if ($enumValue == self::getStorageProfileProtocolCoreValue(KontikiStorageProfileProtocol::KONTIKI))
            {
                return new vKontikiStorageDeleteJobData();
            }
        }
		if ($baseClass == 'VidiunJobData')
		{
			$jobSubType = $constructorArgs["coreJobSubType"];
			if ($jobSubType == self::getStorageProfileProtocolCoreValue(KontikiStorageProfileProtocol::KONTIKI))
			{
				if ($enumValue == BatchJobType::STORAGE_EXPORT)
				{
					return new VidiunKontikiStorageExportJobData();
				}
			 	if ($enumValue == BatchJobType::STORAGE_DELETE)
	            {
	                return new VidiunKontikiStorageDeleteJobData();
	            }
			}
        }
		if ($baseClass == 'VidiunStorageProfile')
        {
            if ($enumValue == self::getStorageProfileProtocolCoreValue(KontikiStorageProfileProtocol::KONTIKI))
            {
                return new VidiunKontikiStorageProfile();
            }
        }
		if ($baseClass =='Form_Partner_BaseStorageConfiguration' && $enumValue == Vidiun_Client_Enum_StorageProfileProtocol::KONTIKI)
		{
			return new Form_KontikiStorageConfiguration();
		}
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue) {
		if($baseClass == 'StorageProfile' && $enumValue == self::getStorageProfileProtocolCoreValue(KontikiStorageProfileProtocol::KONTIKI))
            return 'KontikiStorageProfile';
		
		if ($baseClass == 'Vidiun_Client_Type_StorageProfile' && $enumValue == Vidiun_Client_Enum_StorageProfileProtocol::KONTIKI)
			return 'Vidiun_Client_Kontiki_Type_KontikiStorageProfile';
		
		if ($baseClass == 'DeliveryProfile') {
			if($enumValue == self::getDeliveryProfileType(KontikiDeliveryProfileType::KONTIKI_HTTP))
				return 'DeliveryProfileKontikiHttp';
		}
	}

	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null) {
		if (!$baseEnumName)
		{
			return array('KontikiStorageProfileProtocol', 'KontikiDeliveryProfileType');
		}
		if ($baseEnumName == 'StorageProfileProtocol')
		{
			return array('KontikiStorageProfileProtocol');
		}
		if($baseEnumName == 'DeliveryProfileType')
			return array('KontikiDeliveryProfileType');

		return array();

	}

	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
        $partner = PartnerPeer::retrieveByPK($partnerId);
        if(!$partner)
            return false;

        return $partner->getPluginEnabled(self::PLUGIN_NAME);

	}

	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName() {
		return self::PLUGIN_NAME;

	}

	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}

	public static function getStorageProfileProtocolCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('StorageProfileProtocol', $value);
	}

	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
        return array ('vKontikiManager');
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunContextDataHelper::getContextDataStreamerType()
	 */
	public function getContextDataStreamerType (accessControlScope $scope, $flavorTags, $streamerType)
	{
		$tagsArray = explode(',', $flavorTags);
		if ($tagsArray[0] == self::KONTIKI_ASSET_TAG)
		{
			return PlaybackProtocol::HTTP;
		}
		
		return $streamerType;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunContextDataHelper::getContextDataMediaProtocol()
	 */
	public function getContextDataMediaProtocol (accessControlScope $scope, $flavorTags, $streamerType, $mediaProtocol)
	{
		$tagsArray = explode(',', $flavorTags);
		if ($tagsArray[0] == self::KONTIKI_ASSET_TAG)
		{
			return PlaybackProtocol::HTTP;
		}
		
		return $mediaProtocol;
	}
}
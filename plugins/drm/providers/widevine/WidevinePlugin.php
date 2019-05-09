<?php
/**
 * @package plugins.widevine
 */
class WidevinePlugin extends BaseDrmPlugin implements IVidiunEnumerator, IVidiunServices , IVidiunPermissions, IVidiunObjectLoader, IVidiunEventConsumers, IVidiunTypeExtender, IVidiunSearchDataContributor, IVidiunPending, IVidiunPlaybackContextDataContributor
{
	const PLUGIN_NAME = 'widevine';
	const WIDEVINE_EVENTS_CONSUMER = 'vWidevineEventsConsumer';
	const WIDEVINE_RESPONSE_TYPE = 'widevine';
	const WIDEVINE_ENABLE_DISTRIBUTION_DATES_SYNC_PERMISSION = 'WIDEVINE_ENABLE_DISTRIBUTION_DATES_SYNC';
	const SEARCH_DATA_SUFFIX = 's';
	
	const REGISTER_ASSET_URL_PART = '/registerasset/';
	const GET_ASSET_URL_PART = '/getasset/';
	
	//Default values
	const VIDIUN_PROVIDER = 'vidiun';
	const DEFAULT_POLICY = 'default';
	const DEFAULT_LICENSE_START = '1970-01-01 00:00:01';
	const DEFAULT_LICENSE_END = '2033-05-18 00:00:00';

	
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
		$drmDependency = new VidiunDependency(DrmPlugin::getPluginName());
		
		return array($drmDependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{	
		if(is_null($baseEnumName))
			return array('WidevineConversionEngineType', 'WidevineAssetType', 'WidevinePermissionName', 'WidevineBatchJobType', 'WidevineProviderType', 'WidevineSchemeName');
		if($baseEnumName == 'conversionEngineType')
			return array('WidevineConversionEngineType');
		if($baseEnumName == 'assetType')
			return array('WidevineAssetType');
		if($baseEnumName == 'PermissionName')
			return array('WidevinePermissionName');
		if($baseEnumName == 'BatchJobType')
			return array('WidevineBatchJobType');		
		if($baseEnumName == 'DrmProviderType')
			return array('WidevineProviderType');
		if ($baseEnumName == 'DrmSchemeName')
			return array('WidevineSchemeName');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'VidiunFlavorParams' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return new VidiunWidevineFlavorParams();
	
		if($baseClass == 'VidiunFlavorParamsOutput' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return new VidiunWidevineFlavorParamsOutput();
		
		if($baseClass == 'VidiunFlavorAsset' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return new VidiunWidevineFlavorAsset();
			
		if($baseClass == 'assetParams' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return new WidevineFlavorParams();
	
		if($baseClass == 'assetParamsOutput' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return new WidevineFlavorParamsOutput();
			
		if($baseClass == 'asset' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return new WidevineFlavorAsset();
			
		if($baseClass == 'flavorAsset' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return new WidevineFlavorAsset();
		
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::WIDEVINE)
			return new VWidevineOperationEngine($constructorArgs['params'], $constructorArgs['outFilePath']);
			
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(WidevineConversionEngineType::WIDEVINE))
			return new VDLOperatorWidevine($enumValue);

		if($baseClass == 'VidiunSerializer' && $enumValue == self::WIDEVINE_RESPONSE_TYPE)
			return new VidiunWidevineSerializer();
			
		if ($baseClass == 'VidiunJobData')
		{
		    if ($enumValue == WidevinePlugin::getApiValue(WidevineBatchJobType::WIDEVINE_REPOSITORY_SYNC))
			{
				return new VidiunWidevineRepositorySyncJobData();
			}
		}		
		if($baseClass == 'VidiunDrmProfile' && $enumValue == WidevinePlugin::getWidevineProviderCoreValue())
			return new VidiunWidevineProfile();
		if($baseClass == 'VidiunDrmProfile' && $enumValue == self::getApiValue(WidevineProviderType::WIDEVINE))
			return new VidiunWidevineProfile();

		if($baseClass == 'DrmProfile' && $enumValue == WidevinePlugin::getWidevineProviderCoreValue())
			return new WidevineProfile();

		if (class_exists('Vidiun_Client_Client'))
		{
			if ($baseClass == 'Vidiun_Client_Drm_Type_DrmProfile' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::WIDEVINE)
    		{
    			return new Vidiun_Client_Widevine_Type_WidevineProfile();
    		}
    		if ($baseClass == 'Form_DrmProfileConfigureExtend_SubForm' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::WIDEVINE)
    		{
     			return new Form_WidevineProfileConfigureExtend_SubForm();
    		}	   		

		}

		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{	
		if($baseClass == 'VidiunFlavorParams' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return 'VidiunWidevineFlavorParams';
	
		if($baseClass == 'VidiunFlavorParamsOutput' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return 'VidiunWidevineFlavorParamsOutput';
		
		if($baseClass == 'VidiunFlavorAsset' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return 'VidiunWidevineFlavorAsset';

		if($baseClass == 'assetParams' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return 'WidevineFlavorParams';
	
		if($baseClass == 'assetParamsOutput' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return 'WidevineFlavorParamsOutput';
			
		if($baseClass == 'asset' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return 'WidevineFlavorAsset';
			
		if($baseClass == 'flavorAsset' && $enumValue == WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			return 'WidevineFlavorAsset';			
		
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::WIDEVINE)
			return 'VWidevineOperationEngine';
			
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(WidevineConversionEngineType::WIDEVINE))
			return 'VDLOperatorWidevine';
			
		if($baseClass == 'VidiunSerializer' && $enumValue == self::WIDEVINE_RESPONSE_TYPE)
			return 'VidiunWidevineSerializer';
		
		if ($baseClass == 'VidiunJobData')
		{
		    if ($enumValue == WidevinePlugin::getApiValue(WidevineBatchJobType::WIDEVINE_REPOSITORY_SYNC))
			{
				return 'VidiunWidevineRepositorySyncJobData';
			}
		}		
		if($baseClass == 'VidiunDrmProfile' && $enumValue == WidevinePlugin::getWidevineProviderCoreValue())
			return 'VidiunWidevineProfile';
		if($baseClass == 'VidiunDrmProfile' && $enumValue == self::getApiValue(WidevineProviderType::WIDEVINE))
			return 'VidiunWidevineProfile';

		if($baseClass == 'DrmProfile' && $enumValue == WidevinePlugin::getWidevineProviderCoreValue())
			return 'WidevineProfile';

		if (class_exists('Vidiun_Client_Client'))
		{
			if ($baseClass == 'Vidiun_Client_Drm_Type_DrmProfile' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::WIDEVINE)
    		{
    			return 'Vidiun_Client_Widevine_Type_WidevineProfile';
    		}

    		if ($baseClass == 'Form_DrmProfileConfigureExtend_SubForm' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::WIDEVINE)
    		{
     			return 'Form_WidevineProfileConfigureExtend_SubForm';
    		}	   		
		}
			
		return null;
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getCoreValue($type, $valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore($type, $value);
	}
	
	public static function getConversionEngineCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('conversionEngineType', $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getAssetTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('assetType', $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getWidevineProviderCoreValue()
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . WidevineProviderType::WIDEVINE;
		return vPluginableEnumsManager::apiToCore('DrmProviderType', $value);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunTypeExtender::getExtendedTypes()
	 */
	public static function getExtendedTypes($baseClass, $enumValue) {
		$supportedBaseClasses = array(
			assetPeer::OM_CLASS,
			assetParamsPeer::OM_CLASS,
			assetParamsOutputPeer::OM_CLASS,
		);
		
		if(in_array($baseClass, $supportedBaseClasses) && $enumValue == assetType::FLAVOR)
		{
			return array(
				WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR),
			);
		}
		
		return null;		
	}

	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap() {
		$map = array(
			'widevineDrm' => 'WidevineDrmService',
		);
		return $map;	
	}

	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId) {	
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			return false;
		return $partner->getPluginEnabled(self::PLUGIN_NAME);			
	}
	
	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::WIDEVINE_EVENTS_CONSUMER,
		);
	}
	
	public static function getWidevineAssetIdSearchData($wvAssetId)
	{
		return self::getPluginName() . $wvAssetId . self::SEARCH_DATA_SUFFIX;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSearchDataContributor::getSearchData()
	 */
	public static function getSearchData(BaseObject $object)
	{
		if($object instanceof entry)
		{
			$c = new Criteria();
			$c->add(assetPeer::ENTRY_ID, $object->getId());		
			$flavorType = self::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR);
			$c->add(assetPeer::TYPE, $flavorType);		
			$wvFlavorAssets = assetPeer::doSelect($c);
			if(count($wvFlavorAssets))
			{			
				$searchData = array();
				foreach ($wvFlavorAssets as $wvFlavorAsset) 
				{
					$searchData[] = self::getWidevineAssetIdSearchData($wvFlavorAsset->getWidevineAssetId());
				}				
				return array('plugins_data' => implode(' ', $searchData));
			}
		}
			
		return null;
	}
	
	public static function getWidevineConfigParam($key)
	{
		return DrmPlugin::getConfigParam(self::PLUGIN_NAME, $key);
	}

	public function contributeToPlaybackContextDataResult(entry $entry, vPlaybackContextDataParams $entryPlayingDataParams, vPlaybackContextDataResult $result, vContextDataHelper $contextDataHelper)
	{
		if ($entryPlayingDataParams->getType() == self::BASE_PLUGIN_NAME && self::shouldContributeToPlaybackContext($contextDataHelper->getContextDataResult()->getActions()) && $this->isSupportStreamerTypes($entryPlayingDataParams->getDeliveryProfile()->getStreamerType()))
		{
			foreach ($entryPlayingDataParams->getFlavors() as $flavor)
			{
					if ( !in_array("widevine",explode(",",$flavor->getTags())))
						$result->addToFlavorIdsToRemove($flavor->getId());
			}

			$widevineProfile = DrmProfilePeer::retrieveByProviderAndPartnerID(WidevinePlugin::getWidevineProviderCoreValue(), vCurrentContext::getCurrentPartnerId());
			if (!$widevineProfile)
			{
				$widevineProfile = new WidevineProfile();
				$widevineProfile->setName('default');
			}

			if ($widevineProfile)
			{
				/* @var WidevineProfile $widevineProfile */

				$signingKey = vConf::get('signing_key', 'drm', null);
				if ($signingKey)
				{
					$customDataJson = DrmLicenseUtils::createCustomDataForEntry($entry->getId(), $entryPlayingDataParams->getFlavors(), $signingKey);
					$customDataObject = reset($customDataJson);
					$data = new vDrmPlaybackPluginData();
					$data->setLicenseURL($this->constructUrl($widevineProfile, self::getPluginName(), $customDataObject));
					$data->setScheme($this->getDrmSchemeCoreValue());
					$result->addToPluginData(self::getPluginName(), $data);
				}
			}
		}
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getDrmSchemeCoreValue()
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . WidevineSchemeName::WIDEVINE;
		return vPluginableEnumsManager::apiToCore('DrmSchemeName', $value);
	}


	public function isSupportStreamerTypes($streamerType)
	{
		return in_array($streamerType , array(PlaybackProtocol::HTTP));
	}

	public function constructUrl($widevineProfile, $scheme, $customDataObject)
	{
		return $widevineProfile->getLicenseServerUrl() . "/" . $scheme . "/license?custom_data=" . $customDataObject['custom_data'] . "&signature=" . $customDataObject['signature'];
	}

}

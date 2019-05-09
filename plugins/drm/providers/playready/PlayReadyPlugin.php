<?php
/**
 * @package plugins.playReady
 */
class PlayReadyPlugin extends BaseDrmPlugin implements IVidiunEnumerator, IVidiunServices , IVidiunPermissionsEnabler, IVidiunObjectLoader, IVidiunSearchDataContributor, IVidiunPending, IVidiunApplicationPartialView, IVidiunEventConsumers, IVidiunPlaybackContextDataContributor
{
	const PLUGIN_NAME = 'playReady';
	const SEARCH_DATA_SUFFIX = 's';
	const PLAY_READY_EVENTS_CONSUMER = 'vPlayReadyEventsConsumer';
	
	const ENTRY_CUSTOM_DATA_PLAY_READY_KEY_ID = 'play_ready_key_id';
	const PLAY_READY_TAG = 'playready';
	
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
			return array('PlayReadyLicenseScenario', 'PlayReadyLicenseType', 'PlayReadyProviderType', 'PlayReadySchemeName');
		if($baseEnumName == 'DrmLicenseScenario')
			return array('PlayReadyLicenseScenario');
		if($baseEnumName == 'DrmLicenseType')
			return array('PlayReadyLicenseType');
		if($baseEnumName == 'DrmProviderType')
			return array('PlayReadyProviderType');
		if ($baseEnumName == 'DrmSchemeName')
			return array('PlayReadySchemeName');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'VidiunDrmProfile' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return new VidiunPlayReadyProfile();
		if($baseClass == 'VidiunDrmProfile' && $enumValue == self::getApiValue(PlayReadyProviderType::PLAY_READY))
			return new VidiunPlayReadyProfile();		
	
		if($baseClass == 'VidiunDrmPolicy' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return new VidiunPlayReadyPolicy();
		
		if($baseClass == 'DrmProfile' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return new PlayReadyProfile();
			
		if($baseClass == 'DrmPolicy' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return new PlayReadyPolicy();
			
		if (class_exists('Vidiun_Client_Client'))
		{
			if ($baseClass == 'Vidiun_Client_Drm_Type_DrmProfile' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::PLAY_READY)
    		{
    			return new Vidiun_Client_PlayReady_Type_PlayReadyProfile();
    		}
    		if ($baseClass == 'Form_DrmProfileConfigureExtend_SubForm' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::PLAY_READY)
    		{
     			return new Form_PlayReadyProfileConfigureExtend_SubForm();
    		}	   		
    		
		}
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{	
		if($baseClass == 'VidiunDrmProfile' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return 'VidiunPlayReadyProfile';
		if($baseClass == 'VidiunDrmProfile' && $enumValue == self::getApiValue(PlayReadyProviderType::PLAY_READY))
			return 'VidiunPlayReadyProfile';		
			
		if($baseClass == 'VidiunDrmPolicy' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return 'VidiunPlayReadyPolicy';
		
		if($baseClass == 'DrmProfile' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return 'PlayReadyProfile';
			
		if($baseClass == 'DrmPolicy' && $enumValue == PlayReadyPlugin::getPlayReadyProviderCoreValue())
			return 'PlayReadyPolicy';
			
		if (class_exists('Vidiun_Client_Client'))
		{
			if ($baseClass == 'Vidiun_Client_Drm_Type_DrmProfile' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::PLAY_READY)
    		{
    			return 'Vidiun_Client_PlayReady_Type_PlayReadyProfile';
    		}
    		if ($baseClass == 'Form_DrmProfileConfigureExtend_SubForm' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::PLAY_READY)
    		{
     			return 'Form_PlayReadyProfileConfigureExtend_SubForm';
    		}	   		
    		
		}
			
		return null;
	}

	/* (non-PHPdoc)
	 * @see IVidiunApplicationPartialView::getApplicationPartialViews()
	 */
	public static function getApplicationPartialViews($controller, $action)
	{
		if($controller == 'plugin' && $action == 'DrmProfileConfigureAction')
		{
			return array(
				new Vidiun_View_Helper_PlayReadyProfileConfigure(),
			);
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

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getCoreValue($type, $valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore($type, $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getPlayReadyProviderCoreValue()
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . PlayReadyProviderType::PLAY_READY;
		return vPluginableEnumsManager::apiToCore('DrmProviderType', $value);
	}

	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap() {
		$map = array(
			'playReadyDrm' => 'PlayReadyDrmService',
		);
		return $map;	
	}

	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId) {	
		if (in_array($partnerId, array(Partner::ADMIN_CONSOLE_PARTNER_ID, Partner::BATCH_PARTNER_ID)))
			return true;		
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
			self::PLAY_READY_EVENTS_CONSUMER,
		);
	}
	
	public static function getPlayReadyKeyIdSearchData($keyId)
	{
		return self::getPluginName() . $keyId . self::SEARCH_DATA_SUFFIX;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSearchDataContributor::getSearchData()
	 */
	public static function getSearchData(BaseObject $object)
	{
		if($object instanceof entry)
		{
			$keyId = $object->getFromCustomData(self::ENTRY_CUSTOM_DATA_PLAY_READY_KEY_ID);
			if($keyId)
			{
				$searchData = self::getPlayReadyKeyIdSearchData($keyId);			
				return array('plugins_data' => $searchData);
			}
		}
			
		return null;
	}
	
	public static function getPlayReadyConfigParam($key)
	{
		return DrmPlugin::getConfigParam(self::PLUGIN_NAME, $key);
	}

	/* (non-PHPdoc)
	 * @see IVidiunPermissionsEnabler::permissionEnabled()
	 */
	public static function permissionEnabled($partnerId, $permissionName) 
	{
		if($permissionName == 'PLAYREADY_PLUGIN_PERMISSION')
			vPlayReadyPartnerSetup::setupPartner($partnerId);
		
	}

    public function contributeToPlaybackContextDataResult(entry $entry, vPlaybackContextDataParams $entryPlayingDataParams, vPlaybackContextDataResult $result, vContextDataHelper $contextDataHelper)
	{
		if ($entryPlayingDataParams->getType() == self::BASE_PLUGIN_NAME && self::shouldContributeToPlaybackContext($contextDataHelper->getContextDataResult()->getActions()) && $this->isSupportStreamerTypes($entryPlayingDataParams->getDeliveryProfile()->getStreamerType()) )
		{
			$playReadyProfile = DrmProfilePeer::retrieveByProviderAndPartnerID(PlayReadyPlugin::getPlayReadyProviderCoreValue(), vCurrentContext::getCurrentPartnerId());
			if ($playReadyProfile)
			{
				/* @var PlayReadyProfile $playReadyProfile */

				$signingKey = vConf::get('signing_key', 'drm', null);
				if ($signingKey)
				{
					$customDataJson = DrmLicenseUtils::createCustomDataForEntry($entry->getId(), $entryPlayingDataParams->getFlavors(), $signingKey);
					$customDataObject = reset($customDataJson);
					$data = new vDrmPlaybackPluginData();
					$data->setScheme($this->getDrmSchemeCoreValue());
					$data->setLicenseURL($this->constructUrl($playReadyProfile, self::PLUGIN_NAME, $customDataObject));
					$result->addToPluginData(self::PLUGIN_NAME, $data);
				}
			}
		}
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getDrmSchemeCoreValue()
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . PlayReadySchemeName::PLAYREADY;
		return vPluginableEnumsManager::apiToCore('DrmSchemeName', $value);
	}

	public function isSupportStreamerTypes($streamerType)
	{
		return in_array($streamerType ,array(PlaybackProtocol::SILVER_LIGHT));
	}

	public function constructUrl($playReadyProfile, $scheme, $customDataObject)
	{
		return $playReadyProfile->getLicenseServerUrl() . "/" . $scheme . "/license?custom_data=" . $customDataObject['custom_data'] . "&signature=" . $customDataObject['signature'];
	}
}


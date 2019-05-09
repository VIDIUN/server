<?php
/**
 * @package plugins.fairplay
 */
class FairplayPlugin extends BaseDrmPlugin implements IVidiunEnumerator, IVidiunObjectLoader, IVidiunEntryContextDataContributor, IVidiunPending, IVidiunPlayManifestContributor, IVidiunPlaybackContextDataContributor
{
	const PLUGIN_NAME = 'fairplay';
	const URL_NAME = 'fps';
	const SEARCH_DATA_SUFFIX = 's';

	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}

	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getUrlName()
	{
		return self::URL_NAME;
	}

	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if (is_null($baseEnumName))
			return array('FairplayProviderType', 'FairplaySchemeName');
		if ($baseEnumName == 'DrmProviderType')
			return array('FairplayProviderType');
		if ($baseEnumName == 'DrmSchemeName')
			return array('FairplaySchemeName');
		return array();
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if ($baseClass == 'VidiunDrmProfile' && $enumValue == FairplayPlugin::getFairplayProviderCoreValue() )
			return new VidiunFairplayDrmProfile();
		if ($baseClass == 'DrmProfile' && $enumValue ==  FairplayPlugin::getFairplayProviderCoreValue())
			return new FairplayDrmProfile();

		if (class_exists('Vidiun_Client_Client'))
		{
			if ($baseClass == 'Vidiun_Client_Drm_Type_DrmProfile' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::FAIRPLAY)
			{
				return new Vidiun_Client_Fairplay_Type_FairplayDrmProfile();
			}
			if ($baseClass == 'Form_DrmProfileConfigureExtend_SubForm' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::FAIRPLAY)
			{
				return new Form_FairplayProfileConfigureExtend_SubForm();
			}
		}
		if ($baseClass == 'VidiunPluginData' && $enumValue == self::getPluginName())
			return new VidiunFairplayEntryContextPluginData();
		if ($baseClass == 'VidiunDrmPlaybackPluginData' && $enumValue == 'vFairPlayPlaybackPluginData')
			return new VidiunFairPlayPlaybackPluginData();
		return null;
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if ($baseClass == 'VidiunDrmProfile' && $enumValue == FairplayPlugin::getFairplayProviderCoreValue() )
			return 'VidiunFairplayDrmProfile';
		if ($baseClass == 'DrmProfile' && $enumValue ==  FairplayPlugin::getFairplayProviderCoreValue())
			return 'FairplayDrmProfile';

		if (class_exists('Vidiun_Client_Client'))
		{
			if ($baseClass == 'Vidiun_Client_Drm_Type_DrmProfile' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::FAIRPLAY)
			{
				return 'Vidiun_Client_Fairplay_Type_FairplayDrmProfile';
			}
			if ($baseClass == 'Form_DrmProfileConfigureExtend_SubForm' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::FAIRPLAY)
			{
				return 'Form_FairplayProfileConfigureExtend_SubForm';
			}
		}
		if ($baseClass == 'VidiunPluginData' && $enumValue == self::getPluginName())
			return 'VidiunFairplayEntryContextPluginData';
		return null;
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getFairplayProviderCoreValue()
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . FairplayProviderType::FAIRPLAY;
		return vPluginableEnumsManager::apiToCore('DrmProviderType', $value);
	}

	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId) {
		return DrmPlugin::isAllowedPartner($partnerId);
	}

	public function contributeToEntryContextDataResult(entry $entry, accessControlScope $contextDataParams, vContextDataHelper $contextDataHelper)
	{
		if ($this->shouldContribute($entry))
		{
			$fairplayContextData = new vFairplayEntryContextPluginData();
			$fairplayProfile = DrmProfilePeer::retrieveByProviderAndPartnerID(FairplayPlugin::getFairplayProviderCoreValue(), vCurrentContext::getCurrentPartnerId());
			if (!is_null($fairplayProfile))
			{
				/**
				 * @var FairplayDrmProfile $fairplayProfile
				 */
				$fairplayContextData->publicCertificate = $fairplayProfile->getPublicCertificate();
				return $fairplayContextData;
			}
		}
		return null;
	}

	/**
	 * @param entry $entry
	 * @return bool
	 */
	protected function shouldContribute(entry $entry)
	{
		if ($entry->getAccessControl())
		{
			foreach ($entry->getAccessControl()->getRulesArray() as $rule)
			{
				/**
				 * @var vRule $rule
				 */
				foreach ($rule->getActions() as $action)
				{
					/**
					 * @var vRuleAction $action
					 */
					if ($action->getType() == DrmAccessControlActionType::DRM_POLICY)
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Returns a Vidiun dependency object that defines the relationship between two plugins.
	 *
	 * @return array<VidiunDependency> The Vidiun dependency object
	 */
	public static function dependsOn()
	{
		$drmDependency = new VidiunDependency(DrmPlugin::getPluginName());

		return array($drmDependency);
	}

	public static function getManifestEditors($config)
	{
		$contributors = array();
		if (self::shouldEditManifest($config))
		{
			$contributor = new FairplayManifestEditor();
			$contributor->entryId = $config->entryId;
			$contributors[] = $contributor;
		}
		return $contributors;
	}

	private static function shouldEditManifest($config)
	{
		if($config->rendererClass == 'vM3U8ManifestRenderer' && $config->deliveryProfile->getType() == DeliveryProfileType::VOD_PACKAGER_HLS && $config->deliveryProfile->getAllowFairplayOffline())
			return true;

		return false;
	}

    public function contributeToPlaybackContextDataResult(entry $entry, vPlaybackContextDataParams $entryPlayingDataParams, vPlaybackContextDataResult $result, vContextDataHelper $contextDataHelper)
	{
		if ($entryPlayingDataParams->getType() == self::BASE_PLUGIN_NAME && self::shouldContributeToPlaybackContext($contextDataHelper->getContextDataResult()->getActions()) && $this->isSupportStreamerTypes($entryPlayingDataParams->getDeliveryProfile()->getStreamerType()))
		{
			$fairplayProfile = DrmProfilePeer::retrieveByProviderAndPartnerID(FairplayPlugin::getFairplayProviderCoreValue(), vCurrentContext::getCurrentPartnerId());
			if ($fairplayProfile)
			{
				/* @var FairplayDrmProfile $fairplayProfile */

				$signingKey = vConf::get('signing_key', 'drm', null);
				if ($signingKey)
				{
					$customDataJson = DrmLicenseUtils::createCustomDataForEntry($entry->getId(), $entryPlayingDataParams->getFlavors(), $signingKey);
					$customDataObject = reset($customDataJson);
					$data = new vFairPlayPlaybackPluginData();
					$data->setLicenseURL($this->constructUrl($fairplayProfile, self::getUrlName(), $customDataObject));
					$data->setScheme($this->getDrmSchemeCoreValue());
					$data->setCertificate($fairplayProfile->getPublicCertificate());
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
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . FairplaySchemeName::FAIRPLAY;
		return vPluginableEnumsManager::apiToCore('DrmSchemeName', $value);
	}


	public function isSupportStreamerTypes($streamerType)
	{
		return in_array($streamerType ,array(PlaybackProtocol::APPLE_HTTP));
	}

	public function constructUrl($fairplayProfile, $scheme, $customDataObject)
	{
		return $fairplayProfile->getLicenseServerUrl() . "/" . $scheme . "/license?custom_data=" . $customDataObject['custom_data'] . "&signature=" . $customDataObject['signature'];
	}

}
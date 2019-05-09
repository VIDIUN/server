<?php
/**
 * @package plugins.drm
 */
class DrmPlugin extends BaseDrmPlugin implements IVidiunServices, IVidiunAdminConsolePages, IVidiunPermissions, IVidiunEnumerator, IVidiunObjectLoader, IVidiunEntryContextDataContributor,IVidiunPermissionsEnabler, IVidiunPlaybackContextDataContributor, IVidiunConfigurator
{
	const PLUGIN_NAME = 'drm';

	/* (non-PHPdoc)
     * @see IVidiunPlugin::getPluginName()
     */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}

	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap() {
		$map = array(
			'drmPolicy' => 'DrmPolicyService',
			'drmProfile' => 'DrmProfileService',
            'drmLicenseAccess' => 'DrmLicenseAccessService'
		);
		return $map;	
	}

	/* (non-PHPdoc)
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages()
	{
		$pages = array();
		$pages[] = new DrmProfileListAction();
		$pages[] = new DrmProfileConfigureAction();
		$pages[] = new DrmProfileDeleteAction();
		$pages[] = new DrmAdminApiAction();

		return $pages;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId) {	
		
		if ($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			return true;
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			return false;
		return $partner->getPluginEnabled(self::PLUGIN_NAME);			
	}

	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('DrmPermissionName', 'DrmConversionEngineType', 'DrmAccessControlActionType', 'CencSchemeName' );
		if($baseEnumName == 'PermissionName')
			return array('DrmPermissionName');
        if($baseEnumName == 'conversionEngineType')
            return array('DrmConversionEngineType');
        if($baseEnumName == 'RuleActionType')
            return array('DrmAccessControlActionType');
		if ($baseEnumName == 'DrmSchemeName')
			return array('CencSchemeName');
		return array();
	}

	public static function getConfigParam($configName, $key)
	{
		$config = vConf::getMap($configName);
		if (!is_array($config))
		{
			VidiunLog::err($configName.' config section is not defined');
			return null;
		}

		if (!isset($config[$key]))
		{
			VidiunLog::err('The key '.$key.' was not found in the '.$configName.' config section');
			return null;
		}

		return $config[$key];
	}

    /* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
    public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
    {
        if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::CENC)
            return new VCEncOperationEngine($constructorArgs['params'], $constructorArgs['outFilePath']);
        if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(DrmConversionEngineType::CENC))
            return new VDLOperatorDrm($enumValue);
        if ($baseClass == 'Vidiun_Client_Drm_Type_DrmProfile' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::CENC)
            return new Vidiun_Client_Drm_Type_DrmProfile();
        if($baseClass == 'vRuleAction' && $enumValue == DrmAccessControlActionType::DRM_POLICY)
            return new vAccessControlDrmPolicyAction();
        if($baseClass == 'VidiunRuleAction' && $enumValue == DrmAccessControlActionType::DRM_POLICY)
            return new VidiunAccessControlDrmPolicyAction();
	    if ($baseClass == 'VidiunPluginData' && $enumValue == self::getPluginName())
		    return new VidiunDrmEntryContextPluginData();
	    if ($baseClass == 'VidiunDrmPlaybackPluginData' && $enumValue == 'vDrmPlaybackPluginData')
		    return new VidiunDrmPlaybackPluginData();
        return null;
    }

    /* (non-PHPdoc)
    * @see IVidiunObjectLoader::getObjectClass()
     */
    public static function getObjectClass($baseClass, $enumValue)
    {
        if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::CENC)
            return "VDRMOperationEngine";
        if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(DrmConversionEngineType::CENC))
            return "VDLOperatorrm";
        if($baseClass == 'VidiunDrmProfile' && $enumValue == VidiunDrmProviderType::CENC)
            return "VidiunDrmProfile";
        if($baseClass == 'DrmProfile' && $enumValue == DrmProviderType::CENC)
            return "DrmProfile";
        if ($baseClass == 'Vidiun_Client_Drm_Type_DrmProfile' && $enumValue == Vidiun_Client_Drm_Enum_DrmProviderType::CENC)
            return 'Vidiun_Client_Drm_Type_DrmProfile';
        if($baseClass == 'vRuleAction' && $enumValue == DrmAccessControlActionType::DRM_POLICY)
            return 'vAccessControlDrmPolicyAction';
        if($baseClass == 'VidiunRuleAction' && $enumValue == DrmAccessControlActionType::DRM_POLICY)
            return 'VidiunAccessControlDrmPolicyAction';
	    if ($baseClass == 'VidiunPluginData' && $enumValue == self::getPluginName())
		    return 'VidiunDrmEntryContextPluginData';
        return null;
    }

    /**
     * @return string
     */
    protected static function getApiValue($value)
    {
        return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $value;
    }

    public function contributeToEntryContextDataResult(entry $entry, accessControlScope $contextDataParams, vContextDataHelper $contextDataHelper)
    {
	    if ($this->shouldContribute($entry ))
	    {
		    $signingKey = $this->getSigningKey();
		    if (!is_null($signingKey))
		    {
			    VidiunLog::info("Signing key is '$signingKey'");
			    $customDataJson = DrmLicenseUtils::createCustomData($entry->getId(), $contextDataHelper->getAllowedFlavorAssets(), $signingKey);
			    $drmContextData = new vDrmEntryContextPluginData();
			    $drmContextData->setFlavorData($customDataJson);
			    return $drmContextData;
		    }
	    }
	    return null;
    }

	public function contributeToPlaybackContextDataResult(entry $entry, vPlaybackContextDataParams $entryPlayingDataParams, vPlaybackContextDataResult $result, vContextDataHelper $contextDataHelper)
	{
		if ( $entryPlayingDataParams->getType() == self::BASE_PLUGIN_NAME && self::shouldContributeToPlaybackContext($contextDataHelper->getContextDataResult()->getActions()) && $this->isSupportStreamerTypes($entryPlayingDataParams->getDeliveryProfile()->getStreamerType()))
		{
			$dbProfile = DrmProfilePeer::retrieveByProviderAndPartnerID(VidiunDrmProviderType::CENC, vCurrentContext::getCurrentPartnerId());
			if ($dbProfile)
			{
				$signingKey = $dbProfile->getSigningKey();
				if ($signingKey)
				{
					$customDataJson = DrmLicenseUtils::createCustomDataForEntry($entry->getId(), $entryPlayingDataParams->getFlavors(), $signingKey);
					$customDataObject = reset($customDataJson);

					foreach (CencSchemeName::getAdditionalValues() as $scheme)
					{
						$data = new vDrmPlaybackPluginData();
						$data->setLicenseURL($this->constructUrl($dbProfile, $this->getUrlName($scheme), $customDataObject));
						$data->setScheme($this->getDrmSchemeCoreValue($scheme));
						$result->addToPluginData($scheme, $data);
					}
				}
			}
		}
	}

	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public function getUrlName($scheme)
	{
		switch ($scheme)
		{
			case CencSchemeName::PLAYREADY_CENC:
				return 'cenc/playready';
			case CencSchemeName::WIDEVINE_CENC:
				return 'cenc/widevine';
			default:
				return '';
		}
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getDrmSchemeCoreValue($scheme)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $scheme;
		return vPluginableEnumsManager::apiToCore('DrmSchemeName', $value);
	}

	public function isSupportStreamerTypes($streamerType)
	{
		return in_array($streamerType ,array(PlaybackProtocol::MPEG_DASH));
	}

	public function constructUrl($dbProfile, $scheme, $customDataObject)
	{
		return $dbProfile->getLicenseServerUrl() . "/" . $scheme . "/license?custom_data=" . $customDataObject['custom_data'] . "&signature=" . $customDataObject['signature'];
	}

    private function getSigningKey()
    {
	    $dbProfile = DrmProfilePeer::retrieveByProviderAndPartnerID(VidiunDrmProviderType::CENC, vCurrentContext::getCurrentPartnerId());
	    if (!is_null($dbProfile))
	    {
		    $signingKey = $dbProfile->getSigningKey();
		    return $signingKey;
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

	/* (non-PHPdoc)
	 * @see IVidiunPermissionsEnabler::permissionEnabled()
	 */
	public static function permissionEnabled($partnerId, $permissionName)
	{
		if ($permissionName == 'DRM_PLUGIN_PERMISSION')
		{
			vDrmPartnerSetup::setupPartner($partnerId);
		}
	}

	public static function getConfig($configName)
	{
		$path = dirname(__FILE__) . '/config/drm.ini';
		if($configName == 'admin' && file_exists($path))
			return new Zend_Config_Ini($path);
		return null;
	}
	
	public static function isAllowAdminApi($actionApi = null)
	{
		$currentPermissions = Infra_AclHelper::getCurrentPermissions();
		return ($currentPermissions && in_array(Vidiun_Client_Enum_PermissionName::SYSTEM_ADMIN_DRM_PROFILE_MODIFY, $currentPermissions));
	}

}



<?php
/**
 * 
 * @service playReadyDrm
 * @package plugins.playReady
 * @subpackage api.services
 */
class PlayReadyDrmService extends VidiunBaseService
{	
	const PLAY_READY_BEGIN_DATE_PARAM = 'playReadyBeginDate';
	const PLAY_READY_EXPIRATION_DATE_PARAM = 'playReadyExpirationDate';
	const MYSQL_CODE_DUPLICATE_KEY = 23000;
	
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		if (!PlayReadyPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
			
		$this->applyPartnerFilterForClass('DrmPolicy');
		$this->applyPartnerFilterForClass('DrmProfile');	
		$this->applyPartnerFilterForClass('entry');
		$this->applyPartnerFilterForClass('DrmKey');
	}
	
	/**
	 * Generate key id and content key for PlayReady encryption
	 * 
	 * @action generateKey 
	 * @return VidiunPlayReadyContentKey $response
	 * 
	 */
	public function generateKeyAction()
	{
		$keySeed = $this->getPartnerKeySeed();
		$keyId = vPlayReadyAESContentKeyGenerator::generatePlayReadyKeyId();		
		$contentKey = $this->createContentKeyObject($keySeed, $keyId);
		$response = new VidiunPlayReadyContentKey();
		$response->fromObject($contentKey, $this->getResponseProfile());
		return $response;
	}
	
	/**
	 * Get content keys for input key ids
	 * 
	 * @action getContentKeys
	 * @param string $keyIds - comma separated key id's 
	 * @return VidiunPlayReadyContentKeyArray $response
	 * 
	 */
	public function getContentKeysAction($keyIds)
	{
		$keySeed = $this->getPartnerKeySeed();
		$contentKeysArr = array();
		$keyIdsArr = explode(',', $keyIds);
		foreach ($keyIdsArr as $keyId) 
		{
			$contentKeysArr[] = $this->createContentKeyObject($keySeed, $keyId);
		}	
		$response = VidiunPlayReadyContentKeyArray::fromDbArray($contentKeysArr, $this->getResponseProfile());	
		return $response;
	}

	/**
	 * Get content key and key id for the given entry
	 * 
	 * @action getEntryContentKey
	 * @param string $entryId 
	 * @param bool $createIfMissing
	 * @return VidiunPlayReadyContentKey $response
	 * 
	 */
	public function getEntryContentKeyAction($entryId, $createIfMissing = false)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if(!$entry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		$keySeed = $this->getPartnerKeySeed();
		
		$keyId = $this->getEntryKeyId($entry->getId());
		if(!$keyId && $createIfMissing)
		{
			$drmKey = new DrmKey();
			$drmKey->setPartnerId($entry->getPartnerId());
			$drmKey->setObjectId($entryId);
			$drmKey->setObjectType(DrmKeyObjectType::ENTRY);
			$drmKey->setProvider(PlayReadyPlugin::getPlayReadyProviderCoreValue());
			$keyId = vPlayReadyAESContentKeyGenerator::generatePlayReadyKeyId();
			$drmKey->setDrmKey($keyId);
			try 
			{
				$drmKey->save();
				$entry->putInCustomData(PlayReadyPlugin::ENTRY_CUSTOM_DATA_PLAY_READY_KEY_ID, $keyId);
				$entry->save();
			}
			catch(PropelException $e)
			{
				if($e->getCause() && $e->getCause()->getCode() == self::MYSQL_CODE_DUPLICATE_KEY) //unique constraint
				{
					$keyId = $this->getEntryKeyId($entry->getId());
				}
				else
				{
					throw $e; // Rethrow the unfamiliar exception
				}
			}
		}
		
		if(!$keyId)
			throw new VidiunAPIException(VidiunPlayReadyErrors::FAILED_TO_GET_ENTRY_KEY_ID, $entryId);
			
		$contentKey = $this->createContentKeyObject($keySeed, $keyId);
		$response = new VidiunPlayReadyContentKey();
		$response->fromObject($contentKey, $this->getResponseProfile());
		
		return $response;				
	}
		
	/**
	 * Get Play Ready policy and dates for license creation
	 * 
	 * @action getLicenseDetails
	 * @param string $keyId
	 * @param string $deviceId
	 * @param int $deviceType
	 * @param string $entryId
	 * @param string $referrer 64base encoded  
	 * @return VidiunPlayReadyLicenseDetails $response
	 * 
	 * @throws VidiunErrors::MISSING_MANDATORY_PARAMETER
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunPlayReadyErrors::ENTRY_NOT_FOUND_BY_KEY_ID
	 * @throws VidiunPlayReadyErrors::PLAYREADY_POLICY_NOT_FOUND
	 */
	public function getLicenseDetailsAction($keyId, $deviceId, $deviceType, $entryId = null, $referrer = null)
	{
		VidiunLog::debug('Get Play Ready license details for keyID: '.$keyId);
		
		$entry = $this->getLicenseRequestEntry($keyId, $entryId);

        $referrerDecoded = base64_decode(str_replace(" ", "+", $referrer));
        if (!is_string($referrerDecoded))
            $referrerDecoded = ""; // base64_decode can return binary data
        $drmLU = new DrmLicenseUtils($entry, $referrerDecoded);
        $policyId = $drmLU->getPolicyId();
        if ( !isset($policyId) )
            throw new VidiunAPIException(VidiunPlayReadyErrors::PLAYREADY_POLICY_NOT_FOUND, $entry->getId());

		$dbPolicy = DrmPolicyPeer::retrieveByPK($policyId);
		if(!$dbPolicy)
			throw new VidiunAPIException(VidiunPlayReadyErrors::PLAYREADY_POLICY_OBJECT_NOT_FOUND, $policyId);
			
		list($beginDate, $expirationDate, $removalDate) = $this->calculateLicenseDates($dbPolicy, $entry);

		$policy = new VidiunPlayReadyPolicy();
		$policy->fromObject($dbPolicy, $this->getResponseProfile());
		
		$this->registerDevice($deviceId, $deviceType);
		
		$response = new VidiunPlayReadyLicenseDetails();
		$response->policy = $policy;
		$response->beginDate = $beginDate;
		$response->expirationDate = $expirationDate;
		$response->removalDate = $removalDate;
				
		return $response;
	}

	private function registerDevice($deviceId, $deviceType)
	{
		VidiunLog::debug("device id: ".$deviceId." device type: ".$deviceType);
		//TODO: log for BI
		if($deviceType != 1 && $deviceType != 7) //TODO: verify how to identify the silverlight client
		{
			throw new VidiunAPIException(VidiunPlayReadyErrors::DRM_DEVICE_NOT_SUPPORTED, $deviceType);
		}
	}

	private function getLicenseRequestEntry($keyId, $entryId = null)
	{
		$entry = null;
		
		$keyId = strtolower($keyId);
		
		if(!$keyId)
			throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, "keyId");
		
		if($entryId)
		{
			 $entry = entryPeer::retrieveByPK($entryId); 
			 if(!$entry)
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);	
				
			$entryKeyId = $this->getEntryKeyId($entry->getId());
			if($entryKeyId != $keyId)
				throw new VidiunAPIException(VidiunPlayReadyErrors::KEY_ID_DONT_MATCH, $keyId, $entryKeyId);	
		}
		else 
		{
			$entryFilter = new entryFilter();
			$entryFilter->fields['_like_plugins_data'] = PlayReadyPlugin::getPlayReadyKeyIdSearchData($keyId);
			$entryFilter->setPartnerSearchScope(baseObjectFilter::MATCH_VIDIUN_NETWORK_AND_PRIVATE);
			$c = VidiunCriteria::create(entryPeer::OM_CLASS);				
			$entryFilter->attachToCriteria($c);	
			$c->applyFilters();
			$entries = entryPeer::doSelect($c);
		
			if($entries && count($entries) > 0)
				$entry = $entries[0];
			if(!$entry)
				throw new VidiunAPIException(VidiunPlayReadyErrors::ENTRY_NOT_FOUND_BY_KEY_ID, $keyId);			 				
		}
		
		return $entry;
	}
	
	private function getPartnerKeySeed()
	{
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		$profile = DrmProfilePeer::retrieveByProvider(PlayReadyPlugin::getPlayReadyProviderCoreValue());
		if(!$profile)
			throw new VidiunAPIException(VidiunPlayReadyErrors::PLAYREADY_PROFILE_NOT_FOUND);
		return $profile->getKeySeed();
	}
	
	private function createContentKeyObject($keySeed, $keyId)
	{
		if(!$keyId)
			throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, "keyId");
			
		$contentKeyVal = vPlayReadyAESContentKeyGenerator::generatePlayReadyContentKey($keySeed, $keyId);
		$contentKey = new PlayReadyContentKey();
		$contentKey->setKeyId($keyId);
		$contentKey->setContentKey($contentKeyVal);	

		return $contentKey;
	}

    public function calculateLicenseDates(PlayReadyPolicy $policy, entry $entry)
    {
        $expirationDate = null;
        $removalDate = null;

        $expirationDate = DrmLicenseUtils::calculateExpirationDate($policy, $entry);

        switch($policy->getLicenseRemovalPolicy())
        {
            case PlayReadyLicenseRemovalPolicy::FIXED_FROM_EXPIRATION:
                $removalDate = $expirationDate + dateUtils::DAY*$policy->getLicenseRemovalDuration();
                break;
            case PlayReadyLicenseRemovalPolicy::ENTRY_SCHEDULING_END:
                $removalDate = $entry->getEndDate();
                break;
        }

        //override begin and expiration dates from vs if passed
        if(vCurrentContext::$vs_object)
        {
            $privileges = vCurrentContext::$vs_object->getPrivileges();
            $allParams = explode(',', $privileges);
            foreach($allParams as $param)
            {
                $exParam = explode(':', $param);
                if ($exParam[0] == self::PLAY_READY_BEGIN_DATE_PARAM)
                    $beginDate = $exParam[1];
                if ($exParam[0] == self::PLAY_READY_EXPIRATION_DATE_PARAM)
                    $expirationDate = $exParam[1];
            }
        }

        return array($beginDate, $expirationDate, $removalDate);
    }

    private function getEntryKeyId($entryId)
	{
		$drmKey = DrmKeyPeer::retrieveByUniqueKey($entryId, DrmKeyObjectType::ENTRY, PlayReadyPlugin::getPlayReadyProviderCoreValue());
		if($drmKey)
			return $drmKey->getDrmKey();
		else
			return null;
	}
}

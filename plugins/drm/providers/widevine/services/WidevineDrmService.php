<?php

/**
 * WidevineDrmService serves as a license proxy to a Widevine license server
 * @service widevineDrm
 * @package plugins.widevine
 * @subpackage api.services
 */
class WidevineDrmService extends VidiunBaseService
{	
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('asset');
		$this->applyPartnerFilterForClass('DrmProfile');
		
		if (!WidevinePlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
	}
		
	/**
	 * Get license for encrypted content playback
	 * 
	 * @action getLicense
	 * @param string $flavorAssetId
	 * @param string $referrer 64base encoded  
	 * @return string $response
	 * 
	 */
	public function getLicenseAction($flavorAssetId, $referrer = null)
	{
		VidiunResponseCacher::disableCache();
		
		VidiunLog::debug('get license for flavor asset: '.$flavorAssetId);
		try 
		{
			$requestParams = requestUtils::getRequestParams();
			if(!array_key_exists(WidevineLicenseProxyUtils::ASSETID, $requestParams))
			{
				VidiunLog::err('assetid is missing on the request');
				return WidevineLicenseProxyUtils::createErrorResponse(VidiunWidevineErrorCodes::WIDEVINE_ASSET_ID_CANNOT_BE_NULL, 0);
			}
			$wvAssetId = $requestParams[WidevineLicenseProxyUtils::ASSETID];
				
			$this->validateLicenseRequest($flavorAssetId, $wvAssetId, $referrer);
			$privileges = null;
			$isAdmin = false;
			if(vCurrentContext::$vs_object)
			{
				$privileges = vCurrentContext::$vs_object->getPrivileges();
				$isAdmin = vCurrentContext::$vs_object->isAdmin();
			}
			$response = WidevineLicenseProxyUtils::sendLicenseRequest($requestParams, $privileges, $isAdmin);
		}
		catch(VidiunWidevineLicenseProxyException $e)
		{
			VidiunLog::err($e);
			$response = WidevineLicenseProxyUtils::createErrorResponse($e->getWvErrorCode(), $wvAssetId);
		}
		catch (Exception $e)
		{
			VidiunLog::err($e);
			$response = WidevineLicenseProxyUtils::createErrorResponse(VidiunWidevineErrorCodes::GENERAL_ERROR, $wvAssetId);
		}	
		
		WidevineLicenseProxyUtils::printLicenseResponseStatus($response);
		return $response;
	}
	
	private function validateLicenseRequest($flavorAssetId, $wvAssetId, $referrer64base)
	{
		if(!$flavorAssetId)
			throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::FLAVOR_ASSET_ID_CANNOT_BE_NULL);
				
		$flavorAsset = $this->getFlavorAssetObject($flavorAssetId);

		if($flavorAsset->getType() != WidevinePlugin::getAssetTypeCoreValue(WidevineAssetType::WIDEVINE_FLAVOR))
			throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::WRONG_ASSET_TYPE);
			
		if($wvAssetId != $flavorAsset->getWidevineAssetId())
			throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::FLAVOR_ASSET_ID_DONT_MATCH_WIDEVINE_ASSET_ID);
					
		$entry = entryPeer::retrieveByPK($flavorAsset->getEntryId());
		if(!$entry)
			throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::FLAVOR_ASSET_ID_NOT_FOUND);
			
		$this->validateAccessControl($entry, $flavorAsset, $referrer64base);		
	}
	
	private function validateAccessControl(entry $entry, flavorAsset $flavorAsset, $referrer64base)
	{
		$referrer = base64_decode(str_replace(" ", "+", $referrer64base));
		if (!is_string($referrer))
			$referrer = ""; // base64_decode can return binary data		
		$secureEntryHelper = new VSecureEntryHelper($entry, vCurrentContext::$vs, $referrer, ContextType::PLAY);
		if(!$secureEntryHelper->isVsAdmin())
		{
			if(!$entry->isScheduledNow())
				throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::ENTRY_NOT_SCHEDULED_NOW);
			if($secureEntryHelper->isEntryInModeration())
				throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::ENTRY_MODERATION_ERROR);
		}
			
		if($secureEntryHelper->shouldBlock())
			throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::ACCESS_CONTROL_RESTRICTED);
			
		if(!$secureEntryHelper->isAssetAllowed($flavorAsset))
			throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::FLAVOR_ASSET_ID_NOT_FOUND);
	}
	
	private function getFlavorAssetObject($flavorAssetId)
	{
		try
		{
			if (!vCurrentContext::$vs)
			{
				$flavorAsset = vCurrentContext::initPartnerByAssetId($flavorAssetId);							
				// enforce entitlement
				$this->setPartnerFilters(vCurrentContext::getCurrentPartnerId());
				vEntitlementUtils::initEntitlementEnforcement();
			}
			else 
			{	
				$flavorAsset = assetPeer::retrieveById($flavorAssetId);
			}
			
			if (!$flavorAsset || $flavorAsset->getStatus() == asset::ASSET_STATUS_DELETED)
				throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::FLAVOR_ASSET_ID_NOT_FOUND);		

			return $flavorAsset;
		}
		catch (PropelException $e)
		{
			throw new VidiunWidevineLicenseProxyException(VidiunWidevineErrorCodes::FLAVOR_ASSET_ID_NOT_FOUND);
		}
	}
}

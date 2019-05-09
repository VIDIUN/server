<?php
/**
 * @service zoomVendor
 * @package plugins.vendor
 * @subpackage api.services
 */
class ZoomVendorService extends VidiunBaseService
{

	/**
	 * no partner will be provided by vendors as this called externally and not from vidiun
	 * @param string $actionName
	 * @return bool
	 */
	protected function partnerRequired($actionName)
	{
		if ($actionName == 'oauthValidation' || $actionName == 'recordingComplete')
		{
			return false;
		}
		return true;
	}

	/**
	 * @param $serviceId
	 * @param $serviceName
	 * @param $actionName
	 * @throws VidiunAPIException
	 */
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
	}

	/**
	 *
	 * @action oauthValidation
	 * @return string
	 * @throws Exception
	 */
	public function oauthValidationAction()
	{
		VidiunResponseCacher::disableCache();
		if (!vConf::hasMap('vendor'))
		{
			throw new VidiunAPIException("Vendor configuration file wasn't found!");
		}
		$zoomConfiguration = vConf::get('ZoomAccount', 'vendor');
		$clientId = $zoomConfiguration['clientId'];
		$zoomBaseURL = $zoomConfiguration['ZoomBaseUrl'];
		$redirectUrl = $zoomConfiguration['redirectUrl'];
		$isAdmin = false;
		$tokens = null;
		if (!array_key_exists('code', $_GET))
		{
			$url = $zoomBaseURL . '/oauth/authorize?' . 'response_type=code' . '&client_id=' . $clientId .  '&redirect_uri=' . $redirectUrl;
			ZoomHelper::redirect($url);
		}
		else
		{
			list($tokens, $permissions) = ZoomWrapper::retrieveZoomDataAsArray(ZoomHelper::API_USERS_ME_PERMISSIONS, true);
			list(, $user) = ZoomWrapper::retrieveZoomDataAsArray(ZoomHelper::API_USERS_ME, false, $tokens, null);
			$accountId = $user[ZoomHelper::ACCOUNT_ID];
			$zoomIntegration = VendorIntegrationPeer::retrieveSingleVendorPerPartner($accountId, VendorTypeEnum::ZOOM_ACCOUNT);
			if ($zoomIntegration && $zoomIntegration->getStatus() === VendorStatus::DELETED)
			{
				$zoomIntegration->setStatus(VendorStatus::ACTIVE);
			}
			ZoomHelper::saveNewTokenData($tokens, $accountId, $zoomIntegration);
			$permissions = $permissions['permissions'];
			$isAdmin = ZoomHelper::canConfigureEventSubscription($permissions);
		}
		if ($isAdmin)
		{
			ZoomHelper::loadLoginPage($tokens);
		}
		throw new VidiunAPIException('Only Zoom admins are allowed to access vidiun configuration page, please check your user account');
	}


	/**
	 * @action deAuthorization
	 * @return string
	 * @throws Exception
	 */
	public function deAuthorizationAction()
	{
		http_response_code(VCurlHeaderResponse::HTTP_STATUS_BAD_REQUEST);
		VidiunResponseCacher::disableCache();
		myPartnerUtils::resetAllFilters();
		ZoomHelper::verifyHeaderToken();
		$data = ZoomHelper::getPayloadData();
		$accountId = ZoomHelper::extractAccountIdFromDeAuthPayload($data);
		VidiunLog::info("Zoom changing account id: $accountId status to deleted , user de-authorized the app");
		$zoomIntegration = VendorIntegrationPeer::retrieveSingleVendorPerPartner($accountId, VendorTypeEnum::ZOOM_ACCOUNT);
		if (!$zoomIntegration)
		{
			throw new VidiunAPIException('Zoom Integration data Does Not Exist for current Partner');
		}
		$zoomIntegration->setStatus(VendorStatus::DELETED);
		$zoomIntegration->save();
		http_response_code(VCurlHeaderResponse::HTTP_STATUS_OK);
		return true;
	}

	/**
	 * @action fetchRegistrationPage
	 * @param string $tokensData
	 * @param string $iv
	 * @throws Exception
	 */
	public function fetchRegistrationPageAction($tokensData, $iv)
	{
		VidiunResponseCacher::disableCache();
		$tokensData = base64_decode($tokensData);
		$iv = base64_decode($iv);
		$zoomConfiguration = vConf::get('ZoomAccount', 'vendor');
		$verificationToken = $zoomConfiguration['verificationToken'];
		$tokens = AESEncrypt::decrypt($verificationToken, $tokensData, $iv);
		$tokens = json_decode($tokens, true);
		$accessToken = $tokens[vZoomOauth::ACCESS_TOKEN];
		list($tokens, $zoomUserData) = ZoomWrapper::retrieveZoomDataAsArray(ZoomHelper::API_USERS_ME, false, $tokens, null);
		$accountId = $zoomUserData[ZoomHelper::ACCOUNT_ID];
		/** @var ZoomVendorIntegration $zoomIntegration */
		$zoomIntegration = VendorIntegrationPeer::retrieveSingleVendorPerPartner($accountId,
			VendorTypeEnum::ZOOM_ACCOUNT);
		if ($accessToken !== $tokens[vZoomOauth::ACCESS_TOKEN])
		{
			// token changed -> refresh tokens
			ZoomHelper::saveNewTokenData($tokens, $accountId, $zoomIntegration);
		}
		$partnerId = vCurrentContext::getCurrentPartnerId();
		if ($zoomIntegration && intval($partnerId) !==  $zoomIntegration->getPartnerId() && $partnerId !== 0)
		{
			$zoomIntegration->setPartnerId($partnerId);
			$zoomIntegration->save();
		}
		ZoomHelper::loadSubmitPage($zoomIntegration, $accountId, $this->getVs());
	}


	/**
	 * @action submitRegistration
	 * @param string $defaultUserId
	 * @param string $zoomCategory
	 * @param string $accountId
	 * @param bool $enableRecordingUpload
	 * @param bool $createUserIfNotExist
	 * @return string
	 * @throws PropelException
	 * @throws Exception
	 */
	public function submitRegistrationAction($defaultUserId, $zoomCategory = null, $accountId, $enableRecordingUpload, $createUserIfNotExist)
	{
		VidiunResponseCacher::disableCache();
		$partnerId = vCurrentContext::getCurrentPartnerId();
		$dbUser = vuserPeer::createVuserForPartner($partnerId, $defaultUserId);

		/** @var ZoomVendorIntegration $zoomIntegration */
		$zoomIntegration = VendorIntegrationPeer::retrieveSingleVendorPerPartner($accountId, VendorTypeEnum::ZOOM_ACCOUNT);
		if (!$zoomIntegration)
		{
			$zoomIntegration = new ZoomVendorIntegration();
			$zoomIntegration->setAccountId($accountId);
			$zoomIntegration->setVendorType(VendorTypeEnum::ZOOM_ACCOUNT);
			$zoomIntegration->setPartnerId($partnerId);
		}

		$zoomIntegration->setCreateUserIfNotExist($createUserIfNotExist);

		if($enableRecordingUpload)
		{
			$zoomIntegration->setStatus(VendorStatus::ACTIVE);
		}
		else
		{
			$zoomIntegration->setStatus(VendorStatus::DISABLED);
		}

		$zoomIntegration->setDefaultUserEMail($defaultUserId);
		if ($zoomCategory)
		{
			$zoomIntegration->setZoomCategory($zoomCategory);
			$categoryId = ZoomHelper::createCategoryForZoom($partnerId, $zoomCategory);
			if($categoryId)
			{
				$zoomIntegration->setZoomCategoryId($categoryId);
			}
		}
		if (!$zoomCategory && $zoomIntegration->getZoomCategory() && $zoomIntegration->getZoomCategoryId())
		{
			$zoomIntegration->unsetCategory();
			$zoomIntegration->unsetCategoryId();
		}

		$zoomIntegration->save();
		return true;
	}

	/**
	 * @action recordingComplete
	 * @throws Exception
	 */
	public function recordingCompleteAction()
	{
		VidiunResponseCacher::disableCache();
		myPartnerUtils::resetAllFilters();
		ZoomHelper::verifyHeaderToken();
		$data = ZoomHelper::getPayloadData();
		list($accountId, $downloadToken, $hostEmail, $downloadURLs, $meetingId, $topic) = ZoomHelper::extractDataFromRecordingCompletePayload($data);
		/** @var ZoomVendorIntegration $zoomIntegration */
		$zoomIntegration = VendorIntegrationPeer::retrieveSingleVendorPerPartner($accountId, VendorTypeEnum::ZOOM_ACCOUNT);
		if (!$zoomIntegration)
		{
			throw new VidiunAPIException('Zoom Integration data Does Not Exist for current Partner');
		}
		if($zoomIntegration->getStatus()==VendorStatus::DISABLED)
		{
			VidiunLog::info("Recieved recording complete event from Zoom account {$accountId} while upload is disabled.");
			throw new VidiunAPIException('Uploads are disabled for current Partner');
		}
		$emails = ZoomHelper::extractCoHosts($meetingId, $zoomIntegration, $accountId);
		$emails = ZoomHelper::getValidatedUsers($emails, $zoomIntegration->getPartnerId(), $zoomIntegration->getCreateUserIfNotExist());
		$dbUser = ZoomHelper::getEntryOwner($hostEmail, $zoomIntegration->getDefaultUserEMail(), $zoomIntegration->getPartnerId(), $zoomIntegration->getCreateUserIfNotExist());
		// user logged in - need to re-init vPermissionManager in order to determine current user's permissions
		$vs = null;
		$this->setPartnerFilters($zoomIntegration->getPartnerId());
		vSessionUtils::createVSessionNoValidations($dbUser->getPartnerId() , $dbUser->getPuserId() , $vs, 86400 , false , "" , '*' );
		vCurrentContext::initVsPartnerUser($vs);
		vPermissionManager::init();
		$urls = ZoomHelper::parseDownloadUrls($downloadURLs, $downloadToken);
		ZoomHelper::uploadToVidiun($urls, $dbUser, $zoomIntegration, $emails, $meetingId, $hostEmail, $topic);
	}
}
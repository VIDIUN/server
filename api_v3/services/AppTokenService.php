<?php

/**
 * Manage application authentication tokens
 *
 * @service appToken
 */
class AppTokenService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('AppToken');
	}
	
	/**
	 * Add new application authentication token
	 * 
	 * @action add
	 * @param VidiunAppToken $appToken
	 * @return VidiunAppToken
	 */
	function addAction(VidiunAppToken $appToken)
	{
		$dbAppToken = $appToken->toInsertableObject();
		$dbAppToken->save();
		
		$appToken = new VidiunAppToken();
		$appToken->fromObject($dbAppToken, $this->getResponseProfile());
		return $appToken;
	}
	
	/**
	 * Get application authentication token by ID
	 * 
	 * @action get
	 * @param string $id
	 * @return VidiunAppToken
	 * 
	 * @throws VidiunErrors::APP_TOKEN_ID_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbAppToken = AppTokenPeer::retrieveByPK($id);
		if(!$dbAppToken)
			throw new VidiunAPIException(VidiunErrors::APP_TOKEN_ID_NOT_FOUND, $id);
		
		$appToken = new VidiunAppToken();
		$appToken->fromObject($dbAppToken, $this->getResponseProfile());
		return $appToken;
	}
	
	/**
	 * Update application authentication token by ID
	 * 
	 * @action update
	 * @param string $id
	 * @param VidiunAppToken $appToken
	 * @return VidiunAppToken
	 * 
	 * @throws VidiunErrors::APP_TOKEN_ID_NOT_FOUND
	 */
	function updateAction($id, VidiunAppToken $appToken)
	{
		$dbAppToken = AppTokenPeer::retrieveByPK($id);
		if(!$dbAppToken)
			throw new VidiunAPIException(VidiunErrors::APP_TOKEN_ID_NOT_FOUND, $id);
		
		$appToken->toUpdatableObject($dbAppToken);
		$dbAppToken->save();
		
		$appToken = new VidiunAppToken();
		$appToken->fromObject($dbAppToken, $this->getResponseProfile());
		return $appToken;
	}
	
	/**
	 * Delete application authentication token by ID
	 * 
	 * @action delete
	 * @param string $id
	 * 
	 * @throws VidiunErrors::APP_TOKEN_ID_NOT_FOUND
	 */
	function deleteAction($id)
	{
		$dbAppToken = AppTokenPeer::retrieveByPK($id);
		if(!$dbAppToken)
			throw new VidiunAPIException(VidiunErrors::APP_TOKEN_ID_NOT_FOUND, $id);
		
		$invalidSessionKey = vs::buildSessionIdHash($this->getPartnerId(), $id); 
		invalidSessionPeer::invalidateByKey($invalidSessionKey, invalidSession::INVALID_SESSION_TYPE_SESSION_ID, $dbAppToken->getExpiry());
		$dbAppToken->setStatus(AppTokenStatus::DELETED);
		$dbAppToken->save();
	}
	
	/**
	 * List application authentication tokens by filter and pager
	 * 
	 * @action list
	 * @param VidiunFilterPager $filter
	 * @param VidiunAppTokenFilter $pager
	 * @return VidiunAppTokenListResponse
	 */
	function listAction(VidiunAppTokenFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
			$filter = new VidiunAppTokenFilter();
		
		if(!$pager)
			$pager = new VidiunFilterPager();


		if ($filter->sessionUserIdEqual)
		{
			$vuser = vuserPeer::getVuserByPartnerAndUid ($this->getPartnerId() , $filter->sessionUserIdEqual );
			if($vuser)
				$filter->sessionUserIdEqual = $vuser->getId();
			else
			{
				$response = new VidiunAppTokenListResponse();
				$response->totalCount = 0;
				return $response;
			}
		}

		$c = new Criteria();
		$appTokenFilter = $filter->toObject();
		$appTokenFilter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		
		$list = AppTokenPeer::doSelect($c);
		
		$totalCount = null;
		$resultCount = count($list);
		if($resultCount && ($resultCount < $pager->pageSize))
		{
			$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
		}
		else
		{
			VidiunFilterPager::detachFromCriteria($c);
			$totalCount = AppTokenPeer::doCount($c);
		}
		
		$response = new VidiunAppTokenListResponse();
		$response->totalCount = $totalCount;
		$response->objects = VidiunAppTokenArray::fromDbArray($list, $this->getResponseProfile());
		return $response;
	}
	
	/**
	 * Starts a new VS (vidiun Session) based on an application authentication token ID
	 * 
	 * @action startSession
	 * @param string $id application token ID
	 * @param string $tokenHash a hash [MD5, SHA1, SHA256 and SHA512 are supported] of the current VS concatenated with the application token 
	 * @param string $userId session user ID, will be ignored if a different user ID already defined on the application token
	 * @param VidiunSessionType $type session type, will be ignored if a different session type is already defined on the application token
	 * @param int $expiry session expiry (in seconds), could be overridden by shorter expiry of the application token
	 * @param string $sessionPrivileges session privileges, will be ignored if a similar privilege is already defined on the application token or the privilege is server reserved
	 * @throws VidiunErrors::APP_TOKEN_ID_NOT_FOUND
	 * @return VidiunSessionInfo
	 */
	function startSessionAction($id, $tokenHash, $userId = null, $type = null, $expiry = null, $sessionPrivileges = null)
	{
		$dbAppToken = AppTokenPeer::retrieveByPK($id);
		if(!$dbAppToken)
			throw new VidiunAPIException(VidiunErrors::APP_TOKEN_ID_NOT_FOUND, $id);
		
		if($dbAppToken->getStatus() != AppTokenStatus::ACTIVE)
			throw new VidiunAPIException(VidiunErrors::APP_TOKEN_NOT_ACTIVE, $id);
		
		$appTokenHash = $dbAppToken->calcHash();
		if($appTokenHash !== $tokenHash)
			throw new VidiunAPIException(VidiunErrors::INVALID_APP_TOKEN_HASH);
		
		VidiunResponseCacher::disableCache();
		
		$tokenExpiry = $dbAppToken->getSessionDuration();
		if(!is_null($dbAppToken->getExpiry()))
		{
			$tokenExpiry = min($tokenExpiry, $dbAppToken->getExpiry() - time());
			if($tokenExpiry < 0)
				throw new VidiunAPIException(VidiunErrors::APP_TOKEN_EXPIRED, $id);
		}
		if(!$expiry)
		{
			$expiry = $tokenExpiry;
		}
		$expiry = min($expiry, $tokenExpiry);
		
		if(!is_null($dbAppToken->getSessionType()))
			$type = $dbAppToken->getSessionType();
		if(is_null($type))
			$type = SessionType::USER;
			
		if(!is_null($dbAppToken->getSessionUserId()))
			$userId = $dbAppToken->getSessionUserId();
			
		$partnerId = vCurrentContext::getCurrentPartnerId();
		$partner = PartnerPeer::retrieveByPK($partnerId);
		$secret = $type == SessionType::ADMIN ? $partner->getAdminSecret() : $partner->getSecret();
		
		$privilegesArray = array(
			vs::PRIVILEGE_SESSION_ID => array($id),
			vs::PRIVILEGE_APP_TOKEN => array($id)
		);
		if($dbAppToken->getSessionPrivileges())
		{
			$privilegesArray = array_merge_recursive($privilegesArray, vs::parsePrivileges($dbAppToken->getSessionPrivileges()));
		}

		if($sessionPrivileges)
		{
			$parsedAppSessionPrivilegesArray = vs::parsePrivileges($sessionPrivileges);
			$additionalAllowedSessionPrivliges = vs::retrieveAllowedAppSessionPrivileges($privilegesArray, $parsedAppSessionPrivilegesArray);
			$privilegesArray = array_merge_recursive($privilegesArray, $additionalAllowedSessionPrivliges);
		}

		$privileges = vs::buildPrivileges($privilegesArray);
		
		$vs = vSessionUtils::createVSession($partnerId, $secret, $userId, $expiry, $type, $privileges);
		if(!$vs)
			throw new VidiunAPIException(APIErrors::START_SESSION_ERROR, $partnerId);
			
		$sessionInfo = new VidiunSessionInfo();
		$sessionInfo->vs = $vs->toSecureString();
		$sessionInfo->partnerId = $partnerId;
		$sessionInfo->userId = $userId;
		$sessionInfo->expiry = $vs->valid_until;
		$sessionInfo->sessionType = $type;
		$sessionInfo->privileges = $privileges;
		
		return $sessionInfo;
	}
	
};

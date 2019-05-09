<?php

/**
 * Session service
 *
 * @service session
 * @package api
 * @subpackage services
 */
class SessionService extends VidiunBaseService
{
    
	
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'startWidgetSession') {
			return false;
		}
		return parent::partnerRequired($actionName);
	}
	
	
	/**
	 * Start a session with Vidiun's server.
	 * The result VS is the session key that you should pass to all services that requires a ticket.
	 * 
	 * @action start
	 * @param string $secret Remember to provide the correct secret according to the sessionType you want
	 * @param string $userId
	 * @param VidiunSessionType $type Regular session or Admin session
	 * @param int $partnerId
	 * @param int $expiry VS expiry time in seconds
	 * @param string $privileges 
	 * @return string
	 * @vsIgnored
	 *
	 * @throws APIErrors::START_SESSION_ERROR
	 */
	function startAction($secret, $userId = "", $type = 0, $partnerId = null, $expiry = 86400 , $privileges = null )
	{
		VidiunResponseCacher::disableCache();
		// make sure the secret fits the one in the partner's table
		$vs = "";
		$result = vSessionUtils::startVSession ( $partnerId , $secret , $userId , $vs , $expiry , $type , "" , $privileges );

		if ( $result >= 0 )
	{
		return $vs;
	}
		else
		{
			throw new VidiunAPIException ( APIErrors::START_SESSION_ERROR ,$partnerId );
		}
	}
	
	
	/**
	 * End a session with the Vidiun server, making the current VS invalid.
	 * 
	 * @action end
	 * @vsOptional
	 */
	function endAction()
	{
		VidiunResponseCacher::disableCache();
		
		$vs = $this->getVs();
		if($vs)
			$vs->kill();
	}

	/**
	 * Start an impersonated session with Vidiun's server.
	 * The result VS is the session key that you should pass to all services that requires a ticket.
	 * 
	 * @action impersonate
	 * @param string $secret - should be the secret (admin or user) of the original partnerId (not impersonatedPartnerId).
	 * @param int $impersonatedPartnerId
	 * @param string $userId - impersonated userId
	 * @param VidiunSessionType $type
	 * @param int $partnerId
	 * @param int $expiry VS expiry time in seconds
	 * @param string $privileges 
	 * @return string
	 * @vsIgnored
	 *
	 * @throws APIErrors::START_SESSION_ERROR
	 */
	function impersonateAction($secret, $impersonatedPartnerId, $userId = "", $type = VidiunSessionType::USER, $partnerId = null, $expiry = 86400 , $privileges = null )
	{
		VidiunResponseCacher::disableCache();
		
		// verify that partnerId exists and is in correspondence with given secret
		$result = myPartnerUtils::isValidSecret($partnerId, $secret, "", $expiry, $type);
		if ($result !== true)
		{
			throw new VidiunAPIException ( APIErrors::START_SESSION_ERROR, $partnerId );
		}
				
		// verify partner is allowed to start session for another partner
		$impersonatedPartner = null;
		if (!myPartnerUtils::allowPartnerAccessPartner($partnerId, $this->partnerGroup(), $impersonatedPartnerId))
		{
		    $c = PartnerPeer::getDefaultCriteria();
		    $c->addAnd(PartnerPeer::ID, $impersonatedPartnerId);
		    $impersonatedPartner = PartnerPeer::doSelectOne($c);
		}
		else 
		{
    		// get impersonated partner
    		$impersonatedPartner = PartnerPeer::retrieveByPK($impersonatedPartnerId);
		}
		
		if(!$impersonatedPartner)
		{
			// impersonated partner could not be fetched from the DB
			throw new VidiunAPIException ( APIErrors::START_SESSION_ERROR ,$partnerId );
		}
		
		// set the correct secret according to required session type
		if($type == VidiunSessionType::ADMIN)
		{
			$impersonatedSecret = $impersonatedPartner->getAdminSecret();
		}
		else
		{
			$impersonatedSecret = $impersonatedPartner->getSecret();
		}
		
		// make sure the secret fits the one in the partner's table
		$vs = "";
		$result = vSessionUtils::startVSession ( $impersonatedPartner->getId() , $impersonatedSecret, $userId , $vs , $expiry , $type , "" , $privileges, $partnerId );

		if ( $result >= 0 )
		{
			return $vs;
		}
		else
		{
			throw new VidiunAPIException ( APIErrors::START_SESSION_ERROR ,$partnerId );
		}
	}

	/**
	 * Start an impersonated session with Vidiun's server.
	 * The result VS info contains the session key that you should pass to all services that requires a ticket.
	 * Type, expiry and privileges won't be changed if they're not set
	 * 
	 * @action impersonateByVs
	 * @param string $session The old VS of the impersonated partner
	 * @param VidiunSessionType $type Type of the new VS 
	 * @param int $expiry Expiry time in seconds of the new VS
	 * @param string $privileges Privileges of the new VS
	 * @return VidiunSessionInfo
	 *
	 * @throws APIErrors::START_SESSION_ERROR
	 */
	function impersonateByVsAction($session, $type = null, $expiry = null , $privileges = null)
	{
		VidiunResponseCacher::disableCache();
		
		$oldVS = null;
		try
		{
			$oldVS = vs::fromSecureString($session);
		}
		catch(Exception $e)
		{
			VidiunLog::err($e->getMessage());
			throw new VidiunAPIException(APIErrors::START_SESSION_ERROR, $this->getPartnerId());
		}
		$impersonatedPartnerId = $oldVS->partner_id;
		$impersonatedUserId = $oldVS->user;
		$impersonatedType = $oldVS->type; 
		$impersonatedExpiry = $oldVS->valid_until - time(); 
		$impersonatedPrivileges = $oldVS->privileges;
		
		if(!is_null($type))
			$impersonatedType = $type;
		if(!is_null($expiry)) 
			$impersonatedExpiry = $expiry;
		if($privileges) 
			$impersonatedPrivileges = $privileges;
		
		// verify partner is allowed to start session for another partner
		$impersonatedPartner = null;
		if(!myPartnerUtils::allowPartnerAccessPartner($this->getPartnerId(), $this->partnerGroup(), $impersonatedPartnerId))
		{
			$c = PartnerPeer::getDefaultCriteria();
			$c->addAnd(PartnerPeer::ID, $impersonatedPartnerId);
			$impersonatedPartner = PartnerPeer::doSelectOne($c);
		}
		else
		{
			// get impersonated partner
			$impersonatedPartner = PartnerPeer::retrieveByPK($impersonatedPartnerId);
		}
		
		if(!$impersonatedPartner)
		{
			VidiunLog::err("Impersonated partner [$impersonatedPartnerId ]could not be fetched from the DB");
			throw new VidiunAPIException(APIErrors::START_SESSION_ERROR, $this->getPartnerId());
		}
		
		// set the correct secret according to required session type
		if($impersonatedType == VidiunSessionType::ADMIN)
		{
			$impersonatedSecret = $impersonatedPartner->getAdminSecret();
		}
		else
		{
			$impersonatedSecret = $impersonatedPartner->getSecret();
		}
		
		$sessionInfo = new VidiunSessionInfo();
		
		$result = vSessionUtils::startVSession($impersonatedPartnerId, $impersonatedSecret, $impersonatedUserId, $sessionInfo->vs, $impersonatedExpiry, $impersonatedType, '', $impersonatedPrivileges, $this->getPartnerId());
		if($result < 0)
		{
			VidiunLog::err("Failed starting a session with result [$result]");
			throw new VidiunAPIException(APIErrors::START_SESSION_ERROR, $this->getPartnerId());
		}
	
		$sessionInfo->partnerId = $impersonatedPartnerId;
		$sessionInfo->userId = $impersonatedUserId;
		$sessionInfo->expiry = $impersonatedExpiry;
		$sessionInfo->sessionType = $impersonatedType;
		$sessionInfo->privileges = $impersonatedPrivileges;
		
		return $sessionInfo;
	}

	/**
	 * Parse session key and return its info
	 * 
	 * @action get
	 * @param string $session The VS to be parsed, keep it empty to use current session.
	 * @return VidiunSessionInfo
	 *
	 * @throws APIErrors::START_SESSION_ERROR
	 */
	function getAction($session = null)
	{
		VidiunResponseCacher::disableCache();
		
		if(!$session)
			$session = vCurrentContext::$vs;
		
		$vs = vs::fromSecureString($session);
		
		if (!myPartnerUtils::allowPartnerAccessPartner($this->getPartnerId(), $this->partnerGroup(), $vs->partner_id))
			throw new VidiunAPIException(APIErrors::PARTNER_ACCESS_FORBIDDEN, $this->getPartnerId(), $vs->partner_id);
		
		$sessionInfo = new VidiunSessionInfo();
		$sessionInfo->partnerId = $vs->partner_id;
		$sessionInfo->userId = $vs->user;
		$sessionInfo->expiry = $vs->valid_until;
		$sessionInfo->sessionType = $vs->type;
		$sessionInfo->privileges = $vs->privileges;
		
		return $sessionInfo;
	}
	
	/**
	 * Start a session for Vidiun's flash widgets
	 * 
	 * @action startWidgetSession
	 * @param string $widgetId
	 * @param int $expiry
	 * @return VidiunStartWidgetSessionResponse
	 * @vsIgnored
	 * 
	 * @throws APIErrors::INVALID_WIDGET_ID
	 * @throws APIErrors::MISSING_VS
	 * @throws APIErrors::INVALID_VS
	 * @throws APIErrors::START_WIDGET_SESSION_ERROR
	 */	
	function startWidgetSession ( $widgetId , $expiry = 86400 )
	{
		// make sure the secret fits the one in the partner's table
		$vsStr = "";
		$widget = widgetPeer::retrieveByPK( $widgetId );
		if ( !$widget )
		{
			throw new VidiunAPIException ( APIErrors::INVALID_WIDGET_ID , $widgetId );
		}

		$partnerId = $widget->getPartnerId();

		//$partner = PartnerPeer::retrieveByPK( $partner_id );
		// TODO - see how to decide if the partner has a URL to redirect to


		// according to the partner's policy and the widget's policy - define the privileges of the vs
		// TODO - decide !! - for now only view - any vshow
		$privileges = "view:*,widget:1";
		
		if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENTITLEMENT, $partnerId) &&
			!$widget->getEnforceEntitlement() && $widget->getEntryId())
			$privileges .= ','. vSessionBase::PRIVILEGE_DISABLE_ENTITLEMENT_FOR_ENTRY . ':' . $widget->getEntryId();
			
		if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENTITLEMENT, $partnerId) &&
			!is_null($widget->getPrivacyContext()) && $widget->getPrivacyContext() != '' )
			$privileges .= ','. vSessionBase::PRIVILEGE_PRIVACY_CONTEXT . ':' . $widget->getPrivacyContext();

		$userId = 0;

		// if the widget has a role, pass it in $privileges so it will be embedded in the VS
		// only if we also have an entry to limit the role operations to
		if ($widget->getRoles() != null)
		{
			$roles = explode(",", $widget->getRoles());
			foreach($roles as $role) {
				$privileges .= ',' . vSessionBase::PRIVILEGE_SET_ROLE . ':' . $role;
			}
		}

		if ($widget->getEntryId() != null)
		{
			$privileges .= ',' . vSessionBase::PRIVILEGE_LIMIT_ENTRY . ':' . $widget->getEntryId();
		}

		/*if ( $widget->getSecurityType() == widget::WIDGET_SECURITY_TYPE_FORCE_VS )
		{
			$user = $this->getVuser();
			if ( ! $this->getVS() )// the one from the base class
				throw new VidiunAPIException ( APIErrors::MISSING_VS );

			$widget_partner_id = $widget->getPartnerId();
			$res = vSessionUtils::validateVSession2 ( 1 ,$widget_partner_id  , $user->getId() , $vs_str , $this->vs );
			
			if ( 0 >= $res )
			{
				// chaned this to be an exception rather than an error
				throw new VidiunAPIException ( APIErrors::INVALID_VS , $vs_str , $res , vs::getErrorStr( $res ));
			}			
		}
		else
		{*/
			// 	the session will be for NON admins and privileges of view only
			$result = vSessionUtils::createVSessionNoValidations ( $partnerId , $userId , $vsStr , $expiry , false , "" , $privileges );
		//}

		if ( $result >= 0 )
		{
			$response = new VidiunStartWidgetSessionResponse();
			$response->partnerId = $partnerId;
			$response->vs = $vsStr;
			$response->userId = $userId;
			return $response;
		}
		else
		{
			// TODO - see that there is a good error for when the invalid login count exceed s the max
			throw new  VidiunAPIException  ( APIErrors::START_WIDGET_SESSION_ERROR ,$widgetId );
		}		
	}
}
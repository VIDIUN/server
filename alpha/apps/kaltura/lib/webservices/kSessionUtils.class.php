<?php

class vSessionUtils
{
	const REQUIED_TICKET_NOT_ACCESSIBLE = 'N';
	const REQUIED_TICKET_NONE = 0;
	const REQUIED_TICKET_REGULAR = 1;
	const REQUIED_TICKET_ADMIN = 2;
	
	/**
	 * Will start a vs (always a regular one with view and edit privileges
	 * verification will be done according to the version
	 */
	public static function startVSessionFromLks ( $partner_id , $lvs , $puser_id , $version , &$vs_str  , &$vs,	$desired_expiry_in_seconds=86400 )
	{
		$vs_max_expiry_in_seconds = ""; // see if we want to use the generic setting of the partner
		
		$result = myPartnerUtils::isValidLks ( $partner_id , $lvs , $puser_id , $version , $vs_max_expiry_in_seconds );
		if ( $result >= 0 )
		{
			if ( $vs_max_expiry_in_seconds && $vs_max_expiry_in_seconds < $desired_expiry_in_seconds )
				$desired_expiry_in_seconds = 	$vs_max_expiry_in_seconds;

			$vs = new vs();
			$vs->valid_until = vApiCache::getTime() + $desired_expiry_in_seconds ; // store in milliseconds to make comparison easier at validation time
			$vs->type = vs::TYPE_VS;
			$vs->partner_id = $partner_id;
			$vs->partner_pattern = $partner_id;
			$vs->error = 0;
			$vs->rand = microtime(true);
			$vs->user = $puser_id;
			$vs->privileges = "view:*,edit:*"; // give privileges for view & edit
			$vs_str = $vs->toSecureString();
			return 0;
		}
		else
		{
			return $result;
		}
	}
	
	public static function createVSession($partner_id, $partner_secret, $puser_id, $expiry, $type, $privileges, $additional_data = null, $master_partner_id = null)
	{
		$vs = new vs();
		$vs->valid_until = vApiCache::getTime() + $expiry; // store in milliseconds to make comparison easier at validation time
		$vs->type = $type;
		$vs->partner_id = $partner_id;
		$vs->master_partner_id = $master_partner_id;
		$vs->partner_pattern = $partner_id;
		$vs->error = 0;
		$vs->rand = microtime(true);
		$vs->user = $puser_id;
		$vs->privileges = $privileges;
		$vs->additional_data = $additional_data;
		
		return $vs;
	}
		
	/*
	* will validate the partner_id, secret & key and return a vidiun-session string (VS)
	* the vs will be a 2-way hashed string that expires after a given period of time and holds data about the partner
	* if the partner is a "strong" partner, we may want to return the vs to allow him maipulate other partners (sub partners)
	* this will be done by storing the partner_id_list / partner_id_pattern in the vs.
	* The session can be given per puser - then the puser_id should not be null, OR
	*  it can be global and puser_id = null.
	* In the first case, it will be considered invalid for user that are not the ones that started the session
	*/
	public static function startVSession ( $partner_id , $partner_secret , $puser_id , &$vs_str  ,
		$desired_expiry_in_seconds=86400 , $admin = false , $partner_key = "" , $privileges = "", $master_partner_id = null, $additional_data = null, $enforcePartnerVsMaxExpiry = false)
	{
		$vs_max_expiry_in_seconds = ""; // see if we want to use the generic setting of the partner
		vs::validatePrivileges($privileges,  $partner_id);
		$result =  myPartnerUtils::isValidSecret ( $partner_id , $partner_secret , $partner_key , $vs_max_expiry_in_seconds , $admin );
		if ( $result >= 0 )
		{
			if ( $vs_max_expiry_in_seconds && $vs_max_expiry_in_seconds < $desired_expiry_in_seconds && $enforcePartnerVsMaxExpiry)
				$desired_expiry_in_seconds = 	$vs_max_expiry_in_seconds;

			//	echo "startVSession: from DB: $vs_max_expiry_in_seconds | desired: $desired_expiry_in_seconds " ;

			$vs_type = vs::TYPE_VS;
			if($admin)
				$vs_type = $admin ; // if the admin > 1 - use it rather than automatially setting it to be 2
				
			$vs = self::createVSession($partner_id, $partner_secret, $puser_id, $desired_expiry_in_seconds, $vs_type, $privileges, $additional_data, $master_partner_id);
			$vs_str = $vs->toSecureString();
			return 0;
		}
		else
		{
			return $result;
		}

	}

	public static function createVSessionNoValidations ( $partner_id , $puser_id , &$vs_str  ,
		$desired_expiry_in_seconds=86400 , $admin = false , $partner_key = "" , $privileges = "")
	{
		
		$vs_max_expiry_in_seconds =  myPartnerUtils::getExpiry ( $partner_id );
		if ($vs_max_expiry_in_seconds && ($vs_max_expiry_in_seconds < $desired_expiry_in_seconds))
			$desired_expiry_in_seconds = 	$vs_max_expiry_in_seconds;
		
		$vs = new vs();
		$vs->valid_until = vApiCache::getTime() + $desired_expiry_in_seconds ; // store in milliseconds to make comparison easier at validation time
//			$vs->type = $admin ? vs::TYPE_VAS : vs::TYPE_VS;
		if ( $admin == false )
			$vs->type = vs::TYPE_VS;
		else
			$vs->type = $admin ; // if the admin > 1 - use it rather than automatially setting it to be 2
		
		$vs->partner_id = $partner_id;
		$vs->partner_pattern = $partner_id;
		$vs->error = 0;
		$vs->rand = microtime(true);
		$vs->user = $puser_id;
		$vs->privileges = $privileges;
		$vs_str = $vs->toSecureString();
		return 0;
	}

	/**
	 * @param string $vs_str
	 * @return vs
	 */
	public static function crackVs ( $vs_str )
	{
		$vs = vs::fromSecureString( $vs_str );
		return $vs;
	}
	
	/**
	* will validate the partner_id, secret & key and return a vidiun-admin-session string (VAS)
	* this key will be good for the admin part of the API, such as reports/lists of data/batch deletion
	*/
	public static function startVAdminSession ( $partner_id , $partner_secret , $puser_id , &$vs_str  ,
		$desired_expiry_in_seconds=86400 , $partner_key = "" , $privileges = "")
	{
		return self::startVSession ( $partner_id , $partner_secret , $puser_id , $vs_str  ,	$desired_expiry_in_seconds , true ,  $partner_key , $privileges );
	}

	/*
	 * Will combine all validation methods regardless the ticket type
	 * if the vs exists - use it - it's already cracked but may not be a valid one (it was created before the partner id was known)
	 * the $required_ticket_type can be a number or a list of numbers separated by ',' - this means any of the types is valid
	 * the vs->type can be a number greater than 0.
	 * if the vs->type & required_ticket_type > 0 - it means the vs->type has the relevant bit of the required_ticket_type -
	 * 		consider it a match !
	 * if the required_ticket_type is a list - there should be at least one match for the validation to succeed
	 */
	public static function validateVSession2 ( $required_ticket_type_str , $partner_id , $puser_id , $vs_str ,&$vs)
	{
		$res = 0;
		$required_ticket_type_arr = explode ( ',' , $required_ticket_type_str );
		foreach ( $required_ticket_type_arr as $required_ticket_type )
		{
			$res = vs::INVALID_TYPE; // assume the type is not valid.

			// TODO - remove !!!!!
			$vs_type = $vs->type + 1; // 0->1 and 1->2
 
			// TODO - fix bug ! should work with bitwise operators
			if ( ( $vs_type & $required_ticket_type ) == $required_ticket_type )
			{
				if ($vs_type == self::REQUIED_TICKET_REGULAR )
				{
					$res = $vs->isValid( $partner_id , $puser_id  , vs::TYPE_VS );
				}
				elseif ( $vs_type > self::REQUIED_TICKET_REGULAR )
				{
					// for types greater than 1 (REQUIED_TICKET_REGULAR) - it is assumed the kas was used.
					$res = $vs->isValid( $partner_id , $puser_id  , vs::TYPE_VAS );
				}
			}
			if ( $res > 0 ) return $res;
		}
		return $res;
	}
	
	public static function validateVSessionNoTicket($partner_id, $puser_id, $vs_str, &$vs)
	{
		if ( !$vs_str )
		{
			return false;
		}
		$vs = vs::fromSecureString( $vs_str );
		return $vs->isValid( $partner_id, $puser_id, false );
	}
	
	/**
		validate the time and data of the vs
		If the puser_id was set in the VS, it is expected to be equal to the puser_id here
	*/
	public static function validateVSession ( $partner_id , $puser_id , $vs_str ,&$vs)
	{
		if ( !$vs_str )
		{
			return false;
		}
		$vs = vs::fromSecureString( $vs_str );
		return $vs->isValid( $partner_id , $puser_id  , vs::TYPE_VS );
	}

	public static function validateVAdminSession ( $partner_id , $puser_id , $kas_str ,&$vs)
	{
		if ( !$kas_str )
		{
			return false;
		}

		$kas = vs::fromSecureString( $kas_str );
		return $kas->isValid( $partner_id , $puser_id  , vs::TYPE_VAS );
	}

	public static function killVSession ( $vs )
	{
		try
		{
			$vsObj = vs::fromSecureString($vs);
			if($vsObj)
				$vsObj->kill();
		}
		catch(Exception $e){}
	}
}

class vs extends vSessionBase
{
	const USER_WILDCARD = "*";
	const PRIVILEGE_WILDCARD = "*";

	static $ERROR_MAP = null;
			
	const PATTERN_WILDCARD = "*";
	
	public $error;
	
	/**
	 * @var vuser
	 */
	protected $vuser = null;

	public static function getErrorStr ( $code )
	{
		if ( self::$ERROR_MAP == null )
		{
			self::$ERROR_MAP  = array ( 
				self::INVALID_STR => "INVALID_STR", 
				self::INVALID_PARTNER => "INVALID_PARTNER", 
				self::INVALID_USER => "INVALID_USER", 
				self::INVALID_TYPE => "INVALID_TYPE", 
				self::EXPIRED => "EXPIRED", 
				self::LOGOUT => "LOGOUT", 
				Partner::VALIDATE_LVS_DISABLED => "LVS_DISABLED", 
				self::EXCEEDED_ACTIONS_LIMIT => 'EXCEEDED_ACTIONS_LIMIT', 
				self::EXCEEDED_RESTRICTED_IP => 'EXCEEDED_RESTRICTED_IP', 
				self::EXCEEDED_RESTRICTED_URI => 'EXCEEDED_RESTRICTED_URI', 
			);
		}
		
		$str =  @self::$ERROR_MAP[$code];
		if ( ! $str ) $str = "?";
		return $str;
	}
	
	public function getOriginalString()
	{
		return $this->original_str;
	}
	
	/**
	 * @param string $encoded_str
	 * @return vs
	 */
	public static function fromSecureString ( $encoded_str )
	{
		if(empty($encoded_str))
			return null;

		$vs = new vs();
		if (!$vs->parseVS($encoded_str))
		{
			throw new Exception ( self::getErrorStr ( self::INVALID_STR ) );
		}

		return $vs;
	}

	public function getUniqueString()
	{
		return $this->partner_id . $this->rand;
	}
	
	public function toSecureString()
	{
		list($vsVersion, $secrets) = $this->getVSVersionAndSecret($this->partner_id);
		$secretsArray = explode(',', $secrets);
		$secret = $secretsArray[0]; // first element is always the main Admin Secret
		return vSessionBase::generateSession(
			$vsVersion,
			$secret,
			$this->user,
			$this->type,
			$this->partner_id,
			$this->valid_until - time(),
			$this->privileges,
			$this->master_partner_id,
			$this->additional_data);
	}
	
	public function isValid( $partner_id , $puser_id , $type = false)
	{		
		$result = $this->tryToValidateVS();
		if ($result != self::UNKNOWN && $result != self::OK)
		{
			return $result;
		}
		
		if ( ! $this->matchPartner ( $partner_id ) ) return self::INVALID_PARTNER;
		if ( ! $this->matchUser ( $puser_id ) ) return self::INVALID_USER;
		if ($type !== false) { // do not check vs type
			if ( ! $this->type == $type  ) return self::INVALID_TYPE;
		}
		
		if($result == self::UNKNOWN)
		{
			$criteria = new Criteria();
			
			$vsCriterion = $criteria->getNewCriterion(invalidSessionPeer::TYPE, invalidSession::INVALID_SESSION_TYPE_VS);
			$vsCriterion->addAnd($criteria->getNewCriterion(invalidSessionPeer::VS, $this->getHash()));
			
			$sessionId = $this->getSessionIdHash();
			if($sessionId) {
				$invalidSession = $criteria->getNewCriterion(invalidSessionPeer::VS, $sessionId);
				$invalidSession->addAnd($criteria->getNewCriterion(invalidSessionPeer::TYPE, invalidSession::INVALID_SESSION_TYPE_SESSION_ID));
				$vsCriterion->addOr($invalidSession);
			}
			
			$criteria->add($vsCriterion);
			$dbVs = invalidSessionPeer::doSelectOne($criteria);
			if ($dbVs)
			{
				$currentActionLimit = $dbVs->getActionsLimit();
				if(is_null($currentActionLimit))
					return self::LOGOUT;
				elseif($currentActionLimit <= 0)
					return self::EXCEEDED_ACTIONS_LIMIT;

				$dbVs->setActionsLimit($currentActionLimit - 1);
				$dbVs->save();
			}
			else
			{
				$limit = $this->isSetLimitAction();
				if ($limit)
					invalidSessionPeer::actionsLimitVs($this, $limit - 1);
			}
		}
		
		// creates the vuser
		if($partner_id != Partner::BATCH_PARTNER_ID &&
			PermissionPeer::isValidForPartner(PermissionName::FEATURE_END_USER_REPORTS, $partner_id))
		{
			$this->vuser = vuserPeer::createVuserForPartner($partner_id, $puser_id);
			if(!$puser_id && $this->vuser->getScreenName() != 'Unknown')
			{
				$this->vuser->setScreenName('Unknown');
				$this->vuser->save();
			}
		}
		
		return self::OK;
	}
	
	/**
	 * @return vuser
	 */
	public function getVuser()
	{
		if(!$this->vuser)
			$this->vuser = vuserPeer::getVuserByPartnerAndUid($this->partner_id, $this->user);
			
		return $this->vuser;
	}
	
	/**
	 * @return int
	 */
	public function getVuserId()
	{
		$this->getVuser();
		
		if($this->vuser)
			return $this->vuser->getId();
			
		return null;
	}
	
	public function isValidForPartner($partner_id)
	{
		$result = $this->isValidBase();
		if ($result != self::OK)
		{
			return $result;
		}
		
		if ( ! $this->matchPartner ( $partner_id ) ) return self::INVALID_PARTNER;
		return self::OK;
	}

	// TODO - find a way to verify the privileges -
	// the privileges is a string with a separators and the required_privs is infact a substring
	public function verifyPrivileges ( $required_priv_name , $required_priv_value = null )
	{
		// need the general privilege not a specific value
		if ( empty ( $required_priv_value ) )
			return strpos ( $this->privileges,  $required_priv_name ) !== FALSE ;

		// either the original privileges were general - with a value of a wildcard
		if ( ( $this->privileges == self::PRIVILEGE_WILDCARD ) ||
			 ( strpos ( $this->privileges,  $required_priv_name . ":" . self::PRIVILEGE_WILDCARD ) !== false ) ||
			 ( strpos ( $this->privileges,  $required_priv_name . ":" . $required_priv_value ) !== false ) )
			 {
			 	return true;
			 }
		else if (in_array(self::PRIVILEGE_WILDCARD, $this->parsedPrivileges) ||
		(isset ($this->parsedPrivileges[$required_priv_name]) && in_array($required_priv_value, $this->parsedPrivileges[$required_priv_name])))
		{
			return true;
		}
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		if ( $required_priv_name == vs::PRIVILEGE_EDIT &&
			$this->verifyPlaylistPrivileges(vs::PRIVILEGE_EDIT_ENTRY_OF_PLAYLIST, $required_priv_value, $partnerId))
		{
			return true;
		}
		
	    if ( $required_priv_name == vs::PRIVILEGE_VIEW &&
			$this->verifyPlaylistPrivileges(vs::PRIVILEGE_VIEW_ENTRY_OF_PLAYLIST, $required_priv_value, $partnerId))
		{
			return true;
		}

		if ( $required_priv_name == vs::PRIVILEGE_VIEW &&
			$this->verifyRedirectEntryId(vs::PRIVILEGE_VIEW, $required_priv_value))
		{
			return true;
		}

		return false;
	}

	public function verifyRedirectEntryId($privilegeName, $entryId)
	{
		$allPrivileges = explode(',', $this->privileges);
		foreach($allPrivileges as $privilege)
		{
			$exPrivilege = explode(':', $privilege);
			if ($exPrivilege[0] == $privilegeName && isset($exPrivilege[1]))
			{
				$privilegeObjectId = $exPrivilege[1];
				$entry = entryPeer::retrieveByPK($privilegeObjectId);
				if($entry && $entry->getRedirectEntryId() == $entryId)
					return true;
			}
		}
		return false;
	}
	
	public function verifyPlaylistPrivileges($required_priv_name, $entryId, $partnerId)
	{
		// break all privileges to their pairs - this is to support same "multi-priv" method expected for
		// edit privilege (edit:XXX,edit:YYY,...)
		$allPrivileges = explode(',', $this->privileges);
		// foreach pair - check privileges on playlist
		foreach($allPrivileges as $priv)
		{
			// extract playlist ID from pair
			$exPrivileges = explode(':', $priv);
			if($exPrivileges[0] == $required_priv_name)
			{
				// if found in playlist - return true
				if(myPlaylistUtils::isEntryReferredByPlaylist($entryId, $exPrivileges[1], $partnerId))
				{
					return true;
				}
				
			}
			
		}
		return false;
	}

	public function isSetLimitAction()
	{
		// break all privileges to their pairs - this is to support same "multi-priv" method expected for
		// edit privilege (edit:XXX,edit:YYY,...)
		$allPrivileges = explode(',', $this->privileges);
		// foreach pair - check privileges on playlist
		foreach($allPrivileges as $priv)
		{
			// extract playlist ID from pair
			$exPrivileges = explode(':', $priv);
			if ($exPrivileges[0] == self::PRIVILEGE_ACTIONS_LIMIT)
				if ((is_numeric($exPrivileges[1])) && ($exPrivileges[1] > 0)){
					return $exPrivileges[1];
				}else{
					throw new vCoreException(vCoreException::INTERNAL_SERVER_ERROR, APIErrors::INVALID_ACTIONS_LIMIT);
				}
		}
		
		return false;
	}
		
	public function getEnableEntitlement()
	{
		// break all privileges to their pairs - this is to support same "multi-priv" method expected for
		// edit privilege (edit:XX,edit:YYY,...)
		$allPrivileges = explode(',', $this->privileges);
		// foreach pair - check privileges on playlist
		foreach($allPrivileges as $priv)
		{
			if ($priv == self::PRIVILEGE_ENABLE_ENTITLEMENT)
				return true;
		}
		
		return false;
	}

	public function getDisableEntitlement()
	{
		// break all privileges to their pairs - this is to support same "multi-priv" method expected for
		// edit privilege (edit:XX,edit:YYY,...)
		$allPrivileges = explode(',', $this->privileges);
		// foreach pair - check privileges on playlist
		foreach($allPrivileges as $priv)
		{
			if ($priv == self::PRIVILEGE_DISABLE_ENTITLEMENT)
				return true;
		}
		
		return false;
	}
	
	public function getEnableCategoryModeration()
	{
		// break all privileges to their pairs - this is to support same "multi-priv" method expected for
		// edit privilege (edit:XX,edit:YYY,...)
		$allPrivileges = explode(',', $this->privileges);
		// foreach pair - check privileges on playlist
		foreach($allPrivileges as $priv)
		{
			if ($priv == self::PRIVILEGE_ENABLE_CATEGORY_MODERATION)
				return true;
		}
		
		return false;
	}
	
	public function getDisableEntitlementForEntry()
	{
		$entries = array();
		
		// foreach privileges group
		foreach( $this->parsedPrivileges as $privilegeType => $privileges)
		{
			if ($privilegeType == self::PRIVILEGE_DISABLE_ENTITLEMENT_FOR_ENTRY)
			{
				foreach($privileges as $privilege)
				{
					$entries[] = $privilege;
					$entry = entryPeer::retrieveByPKNoFilter($privilege, null, false);
					if ($entry && $entry->getParentEntryId())
					{
						$entries[] = $entry->getParentEntryId();
					}
				}
			}
		}
		
		return $entries;
	}
	
	public function getPrivilegeByName($privilegeName)
	{
		// edit privilege (edit:XX,edit:YYY,...)
		$allPrivileges = explode(',', $this->privileges);
		// foreach pair - check privileges on playlist
		foreach($allPrivileges as $privilege)
		{
			if ($privilege == $privilegeName)
				return true;
		}
		
		return false;
	}
	
	public function getPrivacyContext()
	{
		// break all privileges to their pairs - this is to support same "multi-priv" method expected for
		// edit privilege (edit:XX,edit:YYY,...)
		$allPrivileges = explode(',', $this->privileges);
		// foreach pair - check privileges on playlist
		
		foreach($allPrivileges as $priv)
		{
			$exPrivileges = explode(':', $priv, 2);
			//validate setRole
			if (count($exPrivileges) == 2 && $exPrivileges[0] == self::PRIVILEGE_PRIVACY_CONTEXT)
				return $exPrivileges[1];
		}
		
		return null;
	}

	public function getSearchContext()
	{
		// break all privileges to their pairs - this is to support same "multi-priv" method expected for
		// edit privilege (edit:XX,edit:YYY,...)
		$allPrivileges = explode(',', $this->privileges);

		foreach($allPrivileges as $priv)
		{
			$exPrivileges = explode(':', $priv, 2);
			if (count($exPrivileges) == 2 && $exPrivileges[0] == self::PRIVILEGE_SEARCH_CONTEXT)
				return $exPrivileges[1];
		}

		return null;
	}

	public function getLimitEntry()
	{
		return $this->getPrivilegeValue(self::PRIVILEGE_LIMIT_ENTRY, null);
	}

	public function getRole()
	{
		// break all privileges to their pairs - this is to support same "multi-priv" method expected for
		// edit privilege (edit:XX,edit:YYY,...)
		$allPrivileges = explode(',', $this->privileges);
		// foreach pair - check privileges on playlist
		foreach($allPrivileges as $priv)
		{
			// extract RoleID from pair
			$exPrivileges = explode(':', $priv);
			if ($exPrivileges[0] == self::PRIVILEGE_SET_ROLE)
			{
				$roleId = isset($exPrivileges[1]) ? $exPrivileges[1] : null; 
				if ($roleId && (is_numeric($roleId)) && ($roleId < 0))
				{
					throw new vCoreException(vCoreException::INTERNAL_SERVER_ERROR, APIErrors::INVALID_SET_ROLE);
				}
				
				return $roleId;
			}
		}
		
		return false;
	}
	
	public static function validatePrivileges ( $privileges, $partnerId )
	{
		// break all privileges to their pairs - this is to support same "multi-priv" method expected for
		// edit privilege (edit:XXX,edit:YYY,...)
		$allPrivileges = explode(',', $privileges);
		// foreach pair - check privileges on playlist
		foreach($allPrivileges as $priv)
		{
			// extract playlist ID from pair
			$exPrivileges = explode(':', $priv);
			//validate setRole
			if ($exPrivileges[0] == self::PRIVILEGE_SET_ROLE){
				$c = new Criteria();
				$c->addAnd(is_numeric($exPrivileges[1]) ? UserRolePeer::ID : UserRolePeer::SYSTEM_NAME, $exPrivileges[1], Criteria::EQUAL);
				$partnerIdsArray = array_map('strval', array($partnerId, PartnerPeer::GLOBAL_PARTNER));
				$c->addAnd(UserRolePeer::PARTNER_ID, $partnerIdsArray, Criteria::IN);
				$roleId = UserRolePeer::doSelectOne($c);
				
				if ($roleId){
					$roleIds = $roleId->getId();
				}else{
					throw new vCoreException(vCoreException::INTERNAL_SERVER_ERROR, APIErrors::UNKNOWN_ROLE_ID ,$exPrivileges[1]);
				}
			}
		}
	}

	public function hasPrivilege($privilegeName)
	{
		if (!is_array($this->parsedPrivileges))
			return false;

		return isset($this->parsedPrivileges[$privilegeName]);
	}

	public function getPrivilegeValues($privilegeName, $default = array())
	{
		if ($this->hasPrivilege($privilegeName))
			return $this->parsedPrivileges[$privilegeName];
		else
			return $default;
	}

	public function getPrivilegeValue($privilegeName, $default = null)
	{
		$values = $this->getPrivilegeValues($privilegeName);
		if (isset($values[0]))
			return $values[0];
		else
			return $default;
	}
	
	private function matchPartner ( $partner_id )
	{
		if ( $this->partner_id == $partner_id ) return true;
		// removed for security reasons - a partner cannot decide to work on other partners
//		if ( $this->partner_pattern == self::PATTERN_WILDCARD ) // TODO - change to some regular expression to match the partner_id
//			return true;
		return false;
	}

	private function matchUser ( $puser_id )
	{
//		echo __METHOD__ . " [{$this->user}] [{$puser_id}]<br>";

		if ( $this->user == null ) return true; // the ticket is a generic one - fits any user
		if ( $this->user == self::USER_WILDCARD  ) return true;// the ticket is a generic one - fits any user

		return $this->user == $puser_id;
	}

	protected function getVSVersionAndSecret($partnerId)
	{
		$result = parent::getVSVersionAndSecret($partnerId);
		if ($result)
			return $result;
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if (!$partner)
			return array(1, null); // VERY big problem

		$vsVersion = $partner->getVSVersion();

		$cacheKey = self::getSecretsCacheKey($partnerId);
		$cacheSections = vCacheManager::getCacheSectionNames(vCacheManager::CACHE_TYPE_PARTNER_SECRETS);
		$adminSecretsAsString = $partner->getAllAdminSecretsAsString();
		foreach ($cacheSections as $cacheSection)
		{
			$cacheStore = vCacheManager::getCache($cacheSection);
			if (!$cacheStore)
				continue;
			$cacheStore->set($cacheKey, array($adminSecretsAsString, $partner->getSecret(), $vsVersion));
		}
		return array($vsVersion, $adminSecretsAsString);
	}

	protected function logError($msg)
	{
		VidiunLog::err($msg);
	}

	public function kill()
	{
		invalidSessionPeer::invalidateVs($this);
	}


	public static function retrieveAllowedAppSessionPrivileges($privilegesArray, $appSessionPrivileges)
	{
		$allowedAppSessionPrivileges = array();
		$serverPrivileges = vSessionBase::getServerPrivileges();
		$privilegesKeys = array_map('trim', array_keys($privilegesArray));
		$forbidenSessionPrivileges = array_merge_recursive($serverPrivileges , $privilegesKeys);

		// allow adding privileges to app token only if they are not in use by the server and were not set on the original app token
		foreach($appSessionPrivileges as $privilegeName => $privilegeValue)
		{
			if(!in_array(trim($privilegeName), $forbidenSessionPrivileges))
				$allowedAppSessionPrivileges[$privilegeName] = $privilegeValue;
		}

		return $allowedAppSessionPrivileges;
	}

}

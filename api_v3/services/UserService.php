<?php
/**
 * Manage partner users on Vidiun's side
 * The userId in vidiun is the unique ID in the partner's system, and the [partnerId,Id] couple are unique key in vidiun's DB
 *
 * @service user
 * @package api
 * @subpackage services
 */
class UserService extends VidiunBaseUserService 
{

	/**
	 * Adds a new user to an existing account in the Vidiun database.
	 * Input param $id is the unique identifier in the partner's system.
	 *
	 * @action add
	 * @param VidiunUser $user The new user
	 * @return VidiunUser The new user
	 *
	 * @throws VidiunErrors::DUPLICATE_USER_BY_ID
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunErrors::UNKNOWN_PARTNER_ID
	 * @throws VidiunErrors::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::DUPLICATE_USER_BY_LOGIN_ID
	 * @throws VidiunErrors::USER_ROLE_NOT_FOUND
	 */
	function addAction(VidiunUser $user)
	{
		if (!preg_match(vuser::PUSER_ID_REGEXP, $user->id))
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'id');
		}

		if ($user instanceof VidiunAdminUser)
		{
			$user->isAdmin = true;
		}

		$lockKey = "user_add_" . $this->getPartnerId() . $user->id;
		return vLock::runLocked($lockKey, array($this, 'adduserImpl'), array($user));
	}

	/**
	 * Updates an existing user object.
	 * You can also use this action to update the userId.
	 * 
	 * @action update
	 * @param string $userId The user's unique identifier in the partner's system
	 * @param VidiunUser $user The user parameters to update
	 * @return VidiunUser The updated user object
	 *
	 * @throws VidiunErrors::INVALID_USER_ID
	 * @throws VidiunErrors::CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER
	 * @throws VidiunErrors::USER_ROLE_NOT_FOUND
	 * @throws VidiunErrors::ACCOUNT_OWNER_NEEDS_PARTNER_ADMIN_ROLE
	 */
	public function updateAction($userId, VidiunUser $user)
	{		
		$dbUser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $userId);
		
		if (!$dbUser)
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);

		if ($dbUser->getIsAdmin() && !is_null($user->isAdmin) && !$user->isAdmin) {
			throw new VidiunAPIException(VidiunErrors::CANNOT_SET_ROOT_ADMIN_AS_NO_ADMIN);
		}

		// update user
		try
		{
			if (!is_null($user->roleIds)) {
				UserRolePeer::testValidRolesForUser($user->roleIds, $this->getPartnerId());
				if ($user->roleIds != $dbUser->getRoleIds() &&
					$dbUser->getId() == $this->getVuser()->getId()) {
					throw new VidiunAPIException(VidiunErrors::CANNOT_CHANGE_OWN_ROLE);
				}
			}
			if (!is_null($user->id) && $user->id != $userId) {
				if(!preg_match(vuser::PUSER_ID_REGEXP, $user->id)) {
					throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'id');
				} 
				
				$existingUser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $user->id);
				if ($existingUser) {
					throw new VidiunAPIException(VidiunErrors::DUPLICATE_USER_BY_ID, $user->id);
				}
			}			
			$dbUser = $user->toUpdatableObject($dbUser);
			$dbUser->save();
		}
		catch (vPermissionException $e)
		{
			$code = $e->getCode();
			if ($code == vPermissionException::ROLE_ID_MISSING) {
				throw new VidiunAPIException(VidiunErrors::ROLE_ID_MISSING);
			}
			if ($code == vPermissionException::ONLY_ONE_ROLE_PER_USER_ALLOWED) {
				throw new VidiunAPIException(VidiunErrors::ONLY_ONE_ROLE_PER_USER_ALLOWED);
			}
			if ($code == vPermissionException::USER_ROLE_NOT_FOUND) {
				throw new VidiunAPIException(VidiunErrors::USER_ROLE_NOT_FOUND);
			}
			if ($code == vPermissionException::ACCOUNT_OWNER_NEEDS_PARTNER_ADMIN_ROLE) {
				throw new VidiunAPIException(VidiunErrors::ACCOUNT_OWNER_NEEDS_PARTNER_ADMIN_ROLE);
			}
			throw $e;
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER) {
				throw new VidiunAPIException(VidiunErrors::CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER);
			}
			throw $e;			
		}
				
		$user = new VidiunUser();
		$user->fromObject($dbUser, $this->getResponseProfile());
		
		return $user;
	}

	
	/**
	 * Retrieves a user object for a specified user ID.
	 * 
	 * @action get
	 * @param string $userId The user's unique identifier in the partner's system
	 * @return VidiunUser The specified user object
	 *
	 * @throws VidiunErrors::INVALID_USER_ID
	 */		
	public function getAction($userId = null)
	{
	    if (is_null($userId) || $userId == '')
	    {
            $userId = vCurrentContext::$vs_uid;	        
	    }

		if (!vCurrentContext::$is_admin_session && vCurrentContext::$vs_uid != $userId)
			throw new VidiunAPIException(VidiunErrors::CANNOT_RETRIEVE_ANOTHER_USER_USING_NON_ADMIN_SESSION, $userId);

		$dbUser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $userId);
	
		if (!$dbUser)
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);

		$user = new VidiunUser();
		$user->fromObject($dbUser, $this->getResponseProfile());
		
		return $user;
	}
	
	/**
	 * Retrieves a user object for a user's login ID and partner ID.
	 * A login ID is the email address used by a user to log into the system.
	 * 
	 * @action getByLoginId
	 * @param string $loginId The user's email address that identifies the user for login
	 * @return VidiunUser The user object represented by the login and partner IDs
	 * 
	 * @throws VidiunErrors::LOGIN_DATA_NOT_FOUND
	 * @throws VidiunErrors::USER_NOT_FOUND
	 */
	public function getByLoginIdAction($loginId)
	{
		$loginData = UserLoginDataPeer::getByEmail($loginId);
		if (!$loginData) {
			throw new VidiunAPIException(VidiunErrors::LOGIN_DATA_NOT_FOUND);
		}
		
		$vuser = vuserPeer::getByLoginDataAndPartner($loginData->getId(), $this->getPartnerId());
		if (!$vuser) {
			throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
		}

		// users that are not publisher administrator are only allowed to get their own object   
		if ($vuser->getId() != vCurrentContext::getCurrentVsVuserId() && !in_array(PermissionName::MANAGE_ADMIN_USERS, vPermissionManager::getCurrentPermissions()))
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $loginId);
		
		$user = new VidiunUser();
		$user->fromObject($vuser, $this->getResponseProfile());
		
		return $user;
	}

	/**
	 * Deletes a user from a partner account.
	 * 
	 * @action delete
	 * @param string $userId The user's unique identifier in the partner's system
	 * @return VidiunUser The deleted user object
	 *
	 * @throws VidiunErrors::INVALID_USER_ID
	 */		
	public function deleteAction($userId)
	{
		$dbUser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $userId);
	
		if (!$dbUser) {
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
		}
					
		try {
			$dbUser->setStatus(VidiunUserStatus::DELETED);
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER) {
				throw new VidiunAPIException(VidiunErrors::CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER);
			}
			throw $e;			
		}
		$dbUser->save();
		
		$user = new VidiunUser();
		$user->fromObject($dbUser, $this->getResponseProfile());
		
		return $user;
	}
	
	/**
	 * Lists user objects that are associated with an account.
	 * Blocked users are listed unless you use a filter to exclude them.
	 * Deleted users are not listed unless you use a filter to include them.
	 * 
	 * @action list
	 * @param VidiunUserFilter $filter A filter used to exclude specific types of users
	 * @param VidiunFilterPager $pager A limit for the number of records to display on a page
	 * @return VidiunUserListResponse The list of user objects
	 */
	public function listAction(VidiunUserFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunUserFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * Notifies that a user is banned from an account.
	 * 
	 * @action notifyBan
	 * @param string $userId The user's unique identifier in the partner's system
	 *
	 * @throws VidiunErrors::INVALID_USER_ID
	 */		
	public function notifyBan($userId)
	{
		$dbUser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $userId);
		if (!$dbUser)
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
		
		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_USER_BANNED, $dbUser);
	}

	/**
	 * Logs a user into a partner account with a partner ID, a partner user ID (puser), and a user password.
	 * 
	 * @action login
	 * @param int $partnerId The identifier of the partner account
	 * @param string $userId The user's unique identifier in the partner's system
	 * @param string $password The user's password
	 * @param int $expiry The requested time (in seconds) before the generated VS expires (By default, a VS expires after 24 hours).
	 * @param string $privileges Special privileges
	 * @return string A session VS for the user
	 * @vsIgnored
	 *
	 * @throws VidiunErrors::USER_NOT_FOUND
	 * @throws VidiunErrors::USER_WRONG_PASSWORD
	 * @throws VidiunErrors::INVALID_PARTNER_ID
	 * @throws VidiunErrors::LOGIN_RETRIES_EXCEEDED
	 * @throws VidiunErrors::LOGIN_BLOCKED
	 * @throws VidiunErrors::PASSWORD_EXPIRED
	 * @throws VidiunErrors::USER_IS_BLOCKED
	 */		
	public function loginAction($partnerId, $userId, $password, $expiry = 86400, $privileges = '*')
	{
		// exceptions might be thrown
		return parent::loginImpl($userId, null, $password, $partnerId, $expiry, $privileges);
	}
	
	/**
	 * Logs a user into a partner account with a user login ID and a user password.
	 * 
	 * @action loginByLoginId
	 * 
	 * @param string $loginId The user's email address that identifies the user for login
	 * @param string $password The user's password
	 * @param int $partnerId The identifier of the partner account
	 * @param int $expiry The requested time (in seconds) before the generated VS expires (By default, a VS expires after 24 hours).
	 * @param string $privileges Special privileges
	 * @param string $otp the user's one-time password
	 * @return string A session VS for the user
	 * @vsIgnored
	 *
	 * @throws VidiunErrors::USER_NOT_FOUND
	 * @throws VidiunErrors::USER_WRONG_PASSWORD
	 * @throws VidiunErrors::INVALID_PARTNER_ID
	 * @throws VidiunErrors::LOGIN_RETRIES_EXCEEDED
	 * @throws VidiunErrors::LOGIN_BLOCKED
	 * @throws VidiunErrors::PASSWORD_EXPIRED
	 * @throws VidiunErrors::USER_IS_BLOCKED
	 */		
	public function loginByLoginIdAction($loginId, $password, $partnerId = null, $expiry = 86400, $privileges = '*', $otp = null)
	{
		// exceptions might be thrown
		return parent::loginImpl(null, $loginId, $password, $partnerId, $expiry, $privileges, $otp);
	}
	
	
	/**
	 * Updates a user's login data: email, password, name.
	 * 
	 * @action updateLoginData
	 * 
	 * @param string $oldLoginId The user's current email address that identified the user for login
	 * @param string $password The user's current email address that identified the user for login
	 * @param string $newLoginId Optional, The user's email address that will identify the user for login
	 * @param string $newPassword Optional, The user's new password
	 * @param string $newFirstName Optional, The user's new first name
	 * @param string $newLastName Optional, The user's new last name
	 * @vsIgnored
	 *
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunErrors::LOGIN_DATA_NOT_FOUND
	 * @throws VidiunErrors::WRONG_OLD_PASSWORD
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::PASSWORD_ALREADY_USED
	 * @throws VidiunErrors::LOGIN_ID_ALREADY_USED
	 */
	public function updateLoginDataAction( $oldLoginId , $password , $newLoginId = "" , $newPassword = "", $newFirstName = null, $newLastName = null)
	{	
		return parent::updateLoginDataImpl($oldLoginId , $password , $newLoginId, $newPassword, $newFirstName, $newLastName);
	}
	
	/**
	 * Reset user's password and send the user an email to generate a new one.
	 * 
	 * @action resetPassword
	 * 
	 * @param string $email The user's email address (login email)
	 * @vsIgnored
	 *
	 * @throws VidiunErrors::LOGIN_DATA_NOT_FOUND
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::PASSWORD_ALREADY_USED
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunErrors::LOGIN_ID_ALREADY_USED
	 */	
	public function resetPasswordAction($email)
	{
		return parent::resetPasswordImpl($email);
	}
	
	/**
	 * Set initial user password
	 * 
	 * @action setInitialPassword
	 * 
	 * @param string $hashKey The hash key used to identify the user (retrieved by email)
	 * @param string $newPassword The new password to set for the user
	 * @vsIgnored
	 *
	 * @throws VidiunErrors::LOGIN_DATA_NOT_FOUND
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::NEW_PASSWORD_HASH_KEY_EXPIRED
	 * @throws VidiunErrors::NEW_PASSWORD_HASH_KEY_INVALID
	 * @throws VidiunErrors::PASSWORD_ALREADY_USED
	 * @throws VidiunErrors::INTERNAL_SERVERL_ERROR
	 */	
	public function setInitialPasswordAction($hashKey, $newPassword)
	{
		return parent::setInitialPasswordImpl($hashKey, $newPassword);
	}
	
	/**
	 * Enables a user to log into a partner account using an email address and a password
	 * 
	 * @action enableLogin
	 * 
	 * @param string $userId The user's unique identifier in the partner's system
	 * @param string $loginId The user's email address that identifies the user for login
	 * @param string $password The user's password
	 * @return VidiunUser The user object represented by the user and login IDs
	 * 
	 * @throws VidiunErrors::USER_LOGIN_ALREADY_ENABLED
	 * @throws VidiunErrors::USER_NOT_FOUND
	 * @throws VidiunErrors::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::LOGIN_ID_ALREADY_USED
	 *
	 */	
	public function enableLoginAction($userId, $loginId, $password = null)
	{		
		try
		{
			$user = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $userId);
			
			if (!$user)
			{
				throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
			}
			
			if (!$user->getIsAdmin() && !$password) {
				throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, 'password');
			}
			
			// Gonen 2011-05-29 : NOTE - 3rd party uses this action and expect that email notification will not be sent by default
			// if this call ever changes make sure you do not change default so mails are sent.
			$user->enableLogin($loginId, $password, true);	
			$user->save();
		}
		catch (Exception $e)
		{
			$code = $e->getCode();
			if ($code == vUserException::USER_LOGIN_ALREADY_ENABLED) {
				throw new VidiunAPIException(VidiunErrors::USER_LOGIN_ALREADY_ENABLED);
			}
			if ($code == vUserException::INVALID_EMAIL) {
				throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
			}
			else if ($code == vUserException::INVALID_PARTNER) {
				throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
			}
			else if ($code == vUserException::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED) {
				throw new VidiunAPIException(VidiunErrors::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED);
			}
			else if ($code == vUserException::PASSWORD_STRUCTURE_INVALID) {
				throw new VidiunAPIException(VidiunErrors::PASSWORD_STRUCTURE_INVALID);
			}
			else if ($code == vUserException::LOGIN_ID_ALREADY_USED) {
				throw new VidiunAPIException(VidiunErrors::LOGIN_ID_ALREADY_USED);
			}
			else if ($code == vUserException::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED) {
				throw new VidiunAPIException(VidiunErrors::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED);
			}
			throw $e;
		}
		
		$apiUser = new VidiunUser();
		$apiUser->fromObject($user, $this->getResponseProfile());
		return $apiUser;
	}
	
	
	
	/**
	 * Disables a user's ability to log into a partner account using an email address and a password.
	 * You may use either a userId or a loginId parameter for this action.
	 * 
	 * @action disableLogin
	 * 
	 * @param string $userId The user's unique identifier in the partner's system
	 * @param string $loginId The user's email address that identifies the user for login
	 * 
	 * @return VidiunUser The user object represented by the user and login IDs
	 * 
	 * @throws VidiunErrors::USER_LOGIN_ALREADY_DISABLED
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws VidiunErrors::USER_NOT_FOUND
	 * @throws VidiunErrors::CANNOT_DISABLE_LOGIN_FOR_ADMIN_USER
	 *
	 */	
	public function disableLoginAction($userId = null, $loginId = null)
	{
		if (!$loginId && !$userId)
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, 'userId');
		}
		
		$user = null;
		try
		{
			if ($loginId)
			{
				$loginData = UserLoginDataPeer::getByEmail($loginId);
				if (!$loginData) {
					throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
				}
				$user = vuserPeer::getByLoginDataAndPartner($loginData->getId(), $this->getPartnerId());
			}
			else
			{
				$user = vuserPeer::getVuserByPartnerAndUid($this->getPArtnerId(), $userId);
			}
			
			if (!$user)
			{
				throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
			}
			
			$user->disableLogin();
		}
		catch (Exception $e)
		{
			$code = $e->getCode();
			if ($code == vUserException::USER_LOGIN_ALREADY_DISABLED) {
				throw new VidiunAPIException(VidiunErrors::USER_LOGIN_ALREADY_DISABLED);
			}
			if ($code == vUserException::CANNOT_DISABLE_LOGIN_FOR_ADMIN_USER) {
				throw new VidiunAPIException(VidiunErrors::CANNOT_DISABLE_LOGIN_FOR_ADMIN_USER);
			}
			throw $e;
		}
		
		$apiUser = new VidiunUser();
		$apiUser->fromObject($user, $this->getResponseProfile());
		return $apiUser;
	}
	
	/**
	 * Index an entry by id.
	 * 
	 * @action index
	 * @param string $id
	 * @param bool $shouldUpdate
	 * @return string 
	 * @throws VidiunErrors::USER_NOT_FOUND
	 */
	function indexAction($id, $shouldUpdate = true)
	{
		$vuser = vuserPeer::getActiveVuserByPartnerAndUid(vCurrentContext::getCurrentPartnerId(), $id);
		
		if (!$vuser)
			throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
		
		$vuser->indexToSearchIndex();
			
		return $vuser->getPuserId();
	}
	
	/**
	 * Logs a user to the destination account provided the VS' user ID is associated with the destination account and the loginData ID matches
	 *
	 * @action loginByVs
	 * @param int $requestedPartnerId
	 * @throws APIErrors::PARTNER_CHANGE_ACCOUNT_DISABLED
	 *
	 * @return VidiunSessionResponse The generated session information
	 * 
	 * @throws VidiunErrors::INVALID_USER_ID
	 * @throws VidiunErrors::PARTNER_CHANGE_ACCOUNT_DISABLED
	 * @throws VidiunErrors::ADMIN_VUSER_NOT_FOUND
	 * @throws VidiunErrors::LOGIN_DATA_NOT_FOUND
	 * @throws VidiunErrors::LOGIN_BLOCKED
	 * @throws VidiunErrors::USER_IS_BLOCKED
	 * @throws VidiunErrors::INTERNAL_SERVERL_ERROR
	 * @throws VidiunErrors::UNKNOWN_PARTNER_ID
	 * @throws VidiunErrors::SERVICE_ACCESS_CONTROL_RESTRICTED
	 * 
	 */
	public function loginByVsAction($requestedPartnerId)
	{
		$this->partnerGroup .= ",$requestedPartnerId";
		$this->applyPartnerFilterForClass('vuser');
		
		$vs = parent::loginByVsImpl($this->getVs()->getOriginalString(), $requestedPartnerId);
		
		$res = new VidiunSessionResponse();
		$res->vs = $vs;
		$res->userId = $this->getVuser()->getPuserId();
		$res->partnerId = $requestedPartnerId;
		
		return $res;
	}
	/**
	 *
	 * Will serve a requested CSV
	 * @action serveCsv
	 * @deprecated use exportCsv.serveCsv
	 *
	 * @param string $id - the requested file id
	 * @return string
	 */
	public function serveCsvAction($id)
	{
		$file_path = ExportCsvService::generateCsvPath($id, $this->getVs());
		return $this->dumpFile($file_path, 'text/csv');
	}
}

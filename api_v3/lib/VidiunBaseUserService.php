<?php
/**
 * @package api
 * @subpackage services
 */
class VidiunBaseUserService extends VidiunBaseService 
{
	
	protected function partnerRequired($actionName)
	{
		$actionName = strtolower($actionName);
		if ($actionName === 'loginbyloginid') {
			return false;
		}
		if ($actionName === 'updatelogindata') {
			return false;
		}
		if ($actionName === 'resetpassword') {
			return false;
		}
		if ($actionName === 'setinitialpassword') {
			return false;
		}
		return parent::partnerRequired($actionName);
	}
	
	
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService ($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('vuser');
	}	
	
	/**
	 * Update admin user password and email
	 * 
	 * @param string $email
	 * @param string $password
	 * @param string $newEmail Optional, provide only when you want to update the email
	 * @param string $newPassword
	 *
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunErrors::LOGIN_DATA_NOT_FOUND
	 * @throws VidiunErrors::WRONG_OLD_PASSWORD
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::PASSWORD_ALREADY_USED
	 * @throws VidiunErrors::LOGIN_ID_ALREADY_USED
	 */
	protected function updateLoginDataImpl( $email , $password , $newEmail = "" , $newPassword = "", $newFirstName, $newLastName)
	{
		VidiunResponseCacher::disableCache();

		$this->validateApiAccessControlByEmail($email);
		
		if ($newEmail != "")
		{
			if(!vString::isEmailString($newEmail))
				throw new VidiunAPIException ( VidiunErrors::INVALID_FIELD_VALUE, "newEmail" );
		}

		try {
			UserLoginDataPeer::updateLoginData ( $email , $password, $newEmail, $newPassword, $newFirstName, $newLastName);
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) {
				throw new VidiunAPIException(VidiunErrors::LOGIN_DATA_NOT_FOUND);
			}
			else if ($code == vUserException::WRONG_PASSWORD) {
				if($password == $newPassword)
					throw new VidiunAPIException(VidiunErrors::USER_WRONG_PASSWORD);
				else
					throw new VidiunAPIException(VidiunErrors::WRONG_OLD_PASSWORD);
			}
			else if ($code == vUserException::PASSWORD_STRUCTURE_INVALID) {
				$c = new Criteria(); 
				$c->add(UserLoginDataPeer::LOGIN_EMAIL, $email ); 
				$loginData = UserLoginDataPeer::doSelectOne($c);
				$invalidPasswordStructureMessage = $loginData->getInvalidPasswordStructureMessage();
				throw new VidiunAPIException(VidiunErrors::PASSWORD_STRUCTURE_INVALID,$invalidPasswordStructureMessage);
			}
			else if ($code == vUserException::PASSWORD_ALREADY_USED) {
				throw new VidiunAPIException(VidiunErrors::PASSWORD_ALREADY_USED);
			}
			else if ($code == vUserException::INVALID_EMAIL) {
				throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'email');
			}
			else if ($code == vUserException::LOGIN_ID_ALREADY_USED) {
				throw new VidiunAPIException(VidiunErrors::LOGIN_ID_ALREADY_USED);
			}
			throw $e;			
		}
	}

	
	/**
	 * Reset admin user password and send it to the users email address
	 * 
	 * @param string $email
	 *
	 * @throws VidiunErrors::LOGIN_DATA_NOT_FOUND
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::PASSWORD_ALREADY_USED
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunErrors::LOGIN_ID_ALREADY_USED
	 */	
	protected function resetPasswordImpl($email)
	{
		VidiunResponseCacher::disableCache();
		
		$this->validateApiAccessControlByEmail($email);
		$this->validateRequestsAmount($email);
		
		try {
			$new_password = UserLoginDataPeer::resetUserPassword($email);
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) {
				throw new VidiunAPIException(VidiunErrors::LOGIN_DATA_NOT_FOUND, "user not found");
			}
			else if ($code == vUserException::PASSWORD_STRUCTURE_INVALID) {
				throw new VidiunAPIException(VidiunErrors::PASSWORD_STRUCTURE_INVALID);
			}
			else if ($code == vUserException::PASSWORD_ALREADY_USED) {
				throw new VidiunAPIException(VidiunErrors::PASSWORD_ALREADY_USED);
			}
			else if ($code == vUserException::INVALID_EMAIL) {
				throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'email');
			}
			else if ($code == vUserException::LOGIN_ID_ALREADY_USED) {
				throw new VidiunAPIException(VidiunErrors::LOGIN_ID_ALREADY_USED);
			}
			throw $e;			
		}	
		
		if (!$new_password)
			throw new VidiunAPIException(VidiunErrors::LOGIN_DATA_NOT_FOUND, "user not found" );
	}

	
	/**
	 * Get a session using user email and password
	 * 
	 * @param string $puserId
	 * @param string $loginEmail
	 * @param string $password
	 * @param int $partnerId
	 * @param int $expiry
	 * @param string $privileges
	 * @param string $otp
	 * 
	 * @return string VS
	 *
	 * @throws VidiunErrors::USER_NOT_FOUND
	 * @thrown VidiunErrors::LOGIN_RETRIES_EXCEEDED
	 * @thrown VidiunErrors::LOGIN_BLOCKED
	 * @thrown VidiunErrors::PASSWORD_EXPIRED
	 * @thrown VidiunErrors::INVALID_PARTNER_ID
	 * @thrown VidiunErrors::INTERNAL_SERVERL_ERROR
	 * @throws VidiunErrors::USER_IS_BLOCKED
	 */		
	protected function loginImpl($puserId, $loginEmail, $password, $partnerId = null, $expiry = 86400, $privileges = '*', $otp = null)
	{
		VidiunResponseCacher::disableCache();
		myPartnerUtils::resetPartnerFilter('vuser');
		vuserPeer::setUseCriteriaFilter(true);
		
		// if a VS of a specific partner is used, don't allow logging in to a different partner
		if ($this->getPartnerId() && $partnerId && $this->getPartnerId() != $partnerId) {
			throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $partnerId);
		}

		if ($loginEmail && !$partnerId) {
			$this->validateApiAccessControlByEmail($loginEmail);
		}
		
		try {
			if ($loginEmail) {
				$user = UserLoginDataPeer::userLoginByEmail($loginEmail, $password, $partnerId, $otp);
			}
			else {
				$user = vuserPeer::userLogin($puserId, $password, $partnerId);
			}
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) {
				throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
			}
			if ($code == vUserException::USER_NOT_FOUND) {
				throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
			}
			else if ($code == vUserException::LOGIN_RETRIES_EXCEEDED) {
				throw new VidiunAPIException(VidiunErrors::LOGIN_RETRIES_EXCEEDED);
			}
			else if ($code == vUserException::LOGIN_BLOCKED) {
				throw new VidiunAPIException(VidiunErrors::LOGIN_BLOCKED);
			}
			else if ($code == vUserException::PASSWORD_EXPIRED) {
				throw new VidiunAPIException(VidiunErrors::PASSWORD_EXPIRED);
			}
			else if ($code == vUserException::WRONG_PASSWORD) {
				throw new VidiunAPIException(VidiunErrors::USER_WRONG_PASSWORD);
			}
			else if ($code == vUserException::USER_IS_BLOCKED) {
				throw new VidiunAPIException(VidiunErrors::USER_IS_BLOCKED);
			}
			else if ($code == vUserException::INVALID_OTP) {
				throw new VidiunAPIException(VidiunErrors::INVALID_OTP);
			}
									
			throw new $e;
		}
		if (!$user) {
			throw new VidiunAPIException(VidiunErrors::LOGIN_DATA_NOT_FOUND);
		}		
		
		if ( ($partnerId && $user->getPartnerId() != $partnerId) ||
		     ($this->getPartnerId() && !$partnerId && $user->getPartnerId() != $this->getPartnerId()) ) {
			throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $partnerId);
		}			
		
		$partner = PartnerPeer::retrieveByPK($user->getPartnerId());
		
		if (!$partner || $partner->getStatus() == Partner::PARTNER_STATUS_FULL_BLOCK)
			throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $user->getPartnerId());
		
		$vs = null;
		
		$admin = $user->getIsAdmin() ? VidiunSessionType::ADMIN : VidiunSessionType::USER;
		// create a vs for this admin_vuser as if entered the admin_secret using the API
		vSessionUtils::createVSessionNoValidations ( $partner->getId() ,  $user->getPuserId() , $vs , $expiry , $admin , "" , $privileges );
		
		return $vs;
	}
	
	
	/**
	 * Set initial users password
	 * 
	 * @param string $hashKey
	 * @param string $newPassword new password to set
	 *
	 * @throws VidiunErrors::LOGIN_DATA_NOT_FOUND
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::NEW_PASSWORD_HASH_KEY_EXPIRED
	 * @throws VidiunErrors::NEW_PASSWORD_HASH_KEY_INVALID
	 * @throws VidiunErrors::PASSWORD_ALREADY_USED
	 * @throws VidiunErrors::INTERNAL_SERVERL_ERROR
	 */	
	protected function setInitialPasswordImpl($hashKey, $newPassword)
	{
		VidiunResponseCacher::disableCache();
		
		try {
			$loginData = UserLoginDataPeer::isHashKeyValid($hashKey);
			if ($loginData)
				$this->validateApiAccessControl($loginData->getLastLoginPartnerId());
			$result = UserLoginDataPeer::setInitialPassword($hashKey, $newPassword);
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) {
				throw new VidiunAPIException(VidiunErrors::LOGIN_DATA_NOT_FOUND);
			}
			if ($code == vUserException::PASSWORD_STRUCTURE_INVALID) {
				$loginData = UserLoginDataPeer::isHashKeyValid($hashKey);
				$invalidPasswordStructureMessage = $loginData->getInvalidPasswordStructureMessage();
				throw new VidiunAPIException(VidiunErrors::PASSWORD_STRUCTURE_INVALID,$invalidPasswordStructureMessage);
			}
			if ($code == vUserException::NEW_PASSWORD_HASH_KEY_EXPIRED) {
				throw new VidiunAPIException(VidiunErrors::NEW_PASSWORD_HASH_KEY_EXPIRED);
			}
			if ($code == vUserException::NEW_PASSWORD_HASH_KEY_INVALID) {
				throw new VidiunAPIException(VidiunErrors::NEW_PASSWORD_HASH_KEY_INVALID);
			}
			if ($code == vUserException::PASSWORD_ALREADY_USED) {
				throw new VidiunAPIException(VidiunErrors::PASSWORD_ALREADY_USED);
			}
			
			throw $e;
		}
		if (!$result) {
			throw new VidiunAPIException(VidiunErrors::INTERNAL_SERVERL_ERROR);
		}
	}
	
	protected function validateApiAccessControlByEmail($email)
	{ 
		$loginData = UserLoginDataPeer::getByEmail($email);
		if ($loginData)
		{
			$this->validateApiAccessControl($loginData->getLastLoginPartnerId());
		}
	}

	/**
	 * check if there were more than max_allowed calls for different resources
	 * @param string $email
	 * @throws VidiunErrors::FAILED_TO_INIT_OBJECT
	 */
	protected function validateRequestsAmount($email)
	{
		$cache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_RESOURCE_RESERVATION);
		if (!$cache)
		{
			throw new VidiunAPIException(VidiunErrors::FAILED_TO_INIT_OBJECT);
		}
		$this->validateRequestsAmountPerEmail($email, $cache);
		$this->validateRequestsAmountPerIp($cache);
	}


	/**
	 * check if there were more than max_allowed calls to reset password action per sepecific email in specific time frame
	 * @param string $email
	 * @param vBaseCacheWrapper $cache
	 * @throws VidiunErrors::RESOURCE_IS_RESERVED
	 */
	protected function validateRequestsAmountPerEmail($email, $cache)
	{
		$resourceId = vCurrentContext::getCurrentPartnerId() . '_' . $email;
		$maxRequestsNum = vConf::get('max_reset_requests_per_email', 'local', 10);
		$reservationTime = vConf::get('reservation_time_per_email', 'local', 600);
		$resourceReservator = new CountingReservation($cache, $reservationTime, $maxRequestsNum);
		if(!$resourceReservator->tryAcquire($resourceId))
		{
			throw new VidiunAPIException(VidiunErrors::RESOURCE_IS_RESERVED, $resourceId);
		}
	}

	/**
	 * check if there were more than max_allowed calls to reset password action per sepecific ip in specific time frame
	 * @param vBaseCacheWrapper $cache
	 * @throws VidiunErrors::RESOURCE_IS_RESERVED
	 */
	protected function validateRequestsAmountPerIp($cache)
	{
		if (!vCurrentContext::$user_ip)
			return;
		$resourceId = 'ip_' . vCurrentContext::$user_ip;
		$maxRequestsNum = vConf::get('max_reset_requests_per_ip', 'local', 100);
		$reservationTime = vConf::get('reservation_time_per_ip', 'local', 600);
		$resourceReservator = new CountingReservation($cache, $reservationTime, $maxRequestsNum);
		if(!$resourceReservator->tryAcquire($resourceId))
		{
			throw new VidiunAPIException(VidiunErrors::RESOURCE_IS_RESERVED, $resourceId);
		}
	}
	
	public function loginByVsImpl($vs, $destPartnerId)
	{
		$vsObj = vSessionUtils::crackVs($vs);
		if($vsObj->partner_id == $destPartnerId)
			return $vs;
		
		if(!$vsObj->user || $vsObj->user == '')
			throw new VidiunAPIException(APIErrors::INVALID_USER_ID, $vsObj->user);

		if($vsObj->hasPrivilege(vSessionBase::PRIVILEGE_ENABLE_PARTNER_CHANGE_ACCOUNT) &&
			!$vsObj->verifyPrivileges(vSessionBase::PRIVILEGE_ENABLE_PARTNER_CHANGE_ACCOUNT, $destPartnerId))
			throw new VidiunAPIException(APIErrors::PARTNER_CHANGE_ACCOUNT_DISABLED);
		
		try 
		{
			$adminVuser = UserLoginDataPeer::userLoginByVs($vs, $destPartnerId, true);
		}
		catch (vUserException $e) 
		{
			$code = $e->getCode();
			if ($code == vUserException::USER_NOT_FOUND) 
			{
				throw new VidiunAPIException(APIErrors::ADMIN_VUSER_NOT_FOUND);
			}
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) 
			{
				throw new VidiunAPIException(APIErrors::LOGIN_DATA_NOT_FOUND);
			}
			else if ($code == vUserException::LOGIN_RETRIES_EXCEEDED) 
			{
				throw new VidiunAPIException(APIErrors::LOGIN_RETRIES_EXCEEDED);
			}
			else if ($code == vUserException::LOGIN_BLOCKED) 
			{
				throw new VidiunAPIException(APIErrors::LOGIN_BLOCKED);
			}
			else if ($code == vUserException::USER_IS_BLOCKED) 
			{
				throw new VidiunAPIException(APIErrors::USER_IS_BLOCKED);
			}
			throw new VidiunAPIException(APIErrors::INTERNAL_SERVERL_ERROR);
		}
		
		if (!$adminVuser || !$adminVuser->getIsAdmin()) 
		{
			throw new VidiunAPIException(APIErrors::ADMIN_VUSER_NOT_FOUND);
		}
		
		if ($destPartnerId != $adminVuser->getPartnerId()) 
		{
			throw new VidiunAPIException(APIErrors::UNKNOWN_PARTNER_ID, $destPartnerId);
		}
		
		$partner = PartnerPeer::retrieveByPK($adminVuser->getPartnerId());
		if (!$partner)
		{
			throw new VidiunAPIException(APIErrors::UNKNOWN_PARTNER_ID, $adminVuser->getPartnerId());
		}
		
		if(!$partner->validateApiAccessControl())
		{
			throw new VidiunAPIException(APIErrors::SERVICE_ACCESS_CONTROL_RESTRICTED, $this->serviceName);
		}
		
		
		vSessionUtils::createVSessionNoValidations ( $partner->getId() ,  $adminVuser->getPuserId() , $vs , dateUtils::DAY , SessionType::ADMIN , "" , $vsObj->getPrivileges() );
		return $vs;
	}
	
	function addUserImpl(VidiunBaseUser $user)
	{
		/* @var $dbUser vuser */
		$dbUser = $user->toInsertableObject();
		$dbUser->setPartnerId($this->getPartnerId());
		try {
			$checkPasswordStructure = isset($user->password) ? true : false;
			$dbUser = vuserPeer::addUser($dbUser, $user->password, $checkPasswordStructure);
		}

		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::USER_ALREADY_EXISTS) {
				throw new VidiunAPIException(VidiunErrors::DUPLICATE_USER_BY_ID, $user->id); //backward compatibility
			}
			if ($code == vUserException::LOGIN_ID_ALREADY_USED) {
				throw new VidiunAPIException(VidiunErrors::DUPLICATE_USER_BY_LOGIN_ID, $user->email); //backward compatibility
			}
			else if ($code == vUserException::USER_ID_MISSING) {
				throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, $user->getFormattedPropertyNameWithClassName('id'));
			}
			else if ($code == vUserException::INVALID_EMAIL) {
				throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'email');
			}
			else if ($code == vUserException::INVALID_PARTNER) {
				throw new VidiunAPIException(VidiunErrors::UNKNOWN_PARTNER_ID);
			}
			else if ($code == vUserException::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED) {
				throw new VidiunAPIException(VidiunErrors::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED);
			}
			else if ($code == vUserException::PASSWORD_STRUCTURE_INVALID) {
				$partner = $dbUser->getPartner();
				$invalidPasswordStructureMessage='';
				if($partner && $partner->getInvalidPasswordStructureMessage())
					$invalidPasswordStructureMessage = $partner->getInvalidPasswordStructureMessage();
				throw new VidiunAPIException(VidiunErrors::PASSWORD_STRUCTURE_INVALID,$invalidPasswordStructureMessage);
			}
			throw $e;
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
			else if ($code == vPermissionException::USER_ROLE_NOT_FOUND) {
				throw new VidiunAPIException(VidiunErrors::USER_ROLE_NOT_FOUND);
			}
			throw $e;
		}

		$className = get_class ($user);
		$newUser = new $className;
		$newUser->fromObject($dbUser, $this->getResponseProfile());

		return $newUser;
	}
}

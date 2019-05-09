<?php


/**
 * Skeleton subclass for performing query and update operations on the 'user_login_data' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package Core
 * @subpackage model
 */
class UserLoginDataPeer extends BaseUserLoginDataPeer implements IRelatedObjectPeer
{
	const VIDIUNS_CMS_PASSWORD_RESET = 51;
	const LAST_LOGIN_TIME_UPDATE_INTERVAL = 600; // 10 Minutes
	
	public static function generateNewPassword()
	{
		$minPassLength = 8;
		$maxPassLength = 14;
		
		$mustCharset[] = 'abcdefghijklmnopqrstuvwxyz';
		$mustCharset[] = '0123456789';
		$mustCharset[] = '~!@#$%^*-=+?()[]{}';
		
		$mustChars = array();
		foreach ($mustCharset as $charset) {
			$mustChars[] = $charset[mt_rand(0, strlen($charset)-1)];
		}
		$newPassword = self::str_makerand($minPassLength-count($mustChars), $maxPassLength-count($mustChars), true, true, true);
		foreach ($mustChars as $c) {
			$i = mt_rand(0, strlen($newPassword));
			$newPassword = substr($newPassword, 0, $i) . $c . substr($newPassword, $i);
		}

		return $newPassword;		
	}
	
	private static function str_makerand ($minlength, $maxlength, $useupper, $usespecial, $usenumbers)
	{
		/*
		Description: string str_makerand(int $minlength, int $maxlength, bool $useupper, bool $usespecial, bool $usenumbers)
		returns a randomly generated string of length between $minlength and $maxlength inclusively.
		
		Notes:
		- If $useupper is true uppercase characters will be used; if false they will be excluded.
		- If $usespecial is true special characters will be used; if false they will be excluded.
		- If $usenumbers is true numerical characters will be used; if false they will be excluded.
		- If $minlength is equal to $maxlength a string of length $maxlength will be returned.
		- Not all special characters are included since they could cause parse errors with queries.
		*/

		$charset = "abcdefghijklmnopqrstuvwxyz";
		if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else $length = mt_rand ($minlength, $maxlength);
		$key = "";
		for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
		return $key;
	}
	
	
	
	private static function emailResetPassword($partner_id, $cms_email, $user_name, $resetPasswordLink)
	{
		vJobsManager::addMailJob(
			null, 
			0, 
			$partner_id, 
			UserLoginDataPeer::VIDIUNS_CMS_PASSWORD_RESET, 
			vMailJobData::MAIL_PRIORITY_NORMAL, 
			vConf::get( "partner_change_email_email" ), 
			vConf::get( "partner_change_email_name" ), 
			$cms_email, 
			array($user_name, $resetPasswordLink)
		);
	}
	
	public static function updateLoginData($oldLoginEmail, $oldPassword, $newLoginEmail = null, $newPassword = null, $newFirstName = null, $newLastName = null)
	{
		// if email is null, no need to do any DB queries
		if (!$oldLoginEmail) {
			throw new vUserException('', vUserException::LOGIN_DATA_NOT_FOUND);
		}

		$c = new Criteria(); 
		$c->add(UserLoginDataPeer::LOGIN_EMAIL, $oldLoginEmail ); 
		$loginData = UserLoginDataPeer::doSelectOne($c);
		
		// check if login data exists
		if (!$loginData) {
			throw new vUserException('', vUserException::LOGIN_DATA_NOT_FOUND);
		}
		
		// if this is an update request (and not just password reset), check that old password is valid
		if ( ($newPassword || $newLoginEmail || $newFirstName || $newLastName) && (!$oldPassword || !$loginData->isPasswordValid ( $oldPassword )) )
		{
			throw new vUserException('', vUserException::WRONG_PASSWORD);
		}
		
		// no need to query the DB if login email is the same
		if ($newLoginEmail === $oldLoginEmail) {
			$newLoginEmail = null;
		}
		
		// check if the email string is a valid email
		if ($newLoginEmail && !vString::isEmailString($newLoginEmail)) {
			throw new vUserException('', vUserException::INVALID_EMAIL);
		}
		
		// check if a user with the new email already exists
		if ($newLoginEmail && UserLoginDataPeer::getByEmail($newLoginEmail)) {
			throw new vUserException('', vUserException::LOGIN_ID_ALREADY_USED);
		}

		self::checkPasswordValidation ( $newPassword, $loginData );
				 
		// update password if requested
		if ($newPassword && $newPassword != $oldPassword) {
			$password = $loginData->resetPassword($newPassword, $oldPassword);
		}
		
		// update email if requested
		if ($newLoginEmail || $newFirstName || $newLastName)
		{
			if ($newLoginEmail) { $loginData->setLoginEmail($newLoginEmail); } // update login email
			if ($newFirstName)  { $loginData->setFirstName($newFirstName);   } // update first name
			if ($newLastName)   { $loginData->setLastName($newLastName);     } // update last name
			
			// update all vusers using this login data, in all partners
			$c = new Criteria();
			$c->addAnd(vuserPeer::LOGIN_DATA_ID, $loginData->getId(), Criteria::EQUAL);
			$c->addAnd(vuserPeer::STATUS, VuserStatus::DELETED, Criteria::NOT_EQUAL);
			vuserPeer::setUseCriteriaFilter(false);
			$vusers = vuserPeer::doSelect($c);
			vuserPeer::setUseCriteriaFilter(true);
			foreach ($vusers as $vuser)
			{
				if ($newLoginEmail) { $vuser->setEmail($newLoginEmail);    } // update login email
				if ($newFirstName)  { $vuser->setFirstName($newFirstName); } // update first name
				if ($newLastName)   { $vuser->setLastName($newLastName);   } // update last name
				$vuser->save();
			}
		}
				
		$loginData->save();
		
		return $loginData;
	}
	
	public static function checkPasswordValidation($newPassword, $loginData) {
		// check that new password structure is valid
		if ($newPassword && 
				  !UserLoginDataPeer::isPasswordStructureValid($newPassword,$loginData->getConfigPartnerId()) ||
				  (stripos($newPassword, $loginData->getFirstName()) !== false)   ||
				  (stripos($newPassword, $loginData->getLastName()) !== false)    ||
				  (stripos($newPassword, $loginData->getFullName()) !== false)    ||
				  ($newPassword == $loginData->getLoginEmail())   ){
			throw new vUserException('', vUserException::PASSWORD_STRUCTURE_INVALID);
		}
		
		// check that password hasn't been used before by this user
		if ($newPassword && $loginData->passwordUsedBefore($newPassword)) {
			throw new vUserException('', vUserException::PASSWORD_ALREADY_USED);
		}
	}

		

	
	public static function resetUserPassword($email)
	{
		$c = new Criteria(); 
		$c->add(UserLoginDataPeer::LOGIN_EMAIL, $email ); 
		$loginData = UserLoginDataPeer::doSelectOne($c);
		
		// check if login data exists
		if (!$loginData) {
			throw new vUserException('', vUserException::LOGIN_DATA_NOT_FOUND);
		}
		
		$partnerId = $loginData->getConfigPartnerId();
		$partner = PartnerPeer::retrieveByPK($partnerId);
		// If on the partner it's set not to reset the password - skip the email sending
		if($partner->getEnabledService(PermissionName::FEATURE_DISABLE_RESET_PASSWORD_EMAIL)) {
			VidiunLog::log("Skipping reset-password email sending according to partner configuration.");
			return true;
		}
		
		$loginData->setPasswordHashKey($loginData->newPassHashKey());
		$loginData->save();
				
		self::emailResetPassword(0, $loginData->getLoginEmail(), $loginData->getFullName(), self::getPassResetLink($loginData->getPasswordHashKey()));
		return true;
	}
	
	/**
	 * @param string $email
	 * @return UserLoginData
	 */
	public static function getByEmail($email)
	{
		$c = new Criteria();
		$c->add ( UserLoginDataPeer::LOGIN_EMAIL , $email );
		$data = UserLoginDataPeer::doSelectOne( $c );
		return $data;
		
	}
	
	public static function isPasswordStructureValid($pass,$partnerId = null)
	{
		if(vCurrentContext::getCurrentPartnerId() == Partner::ADMIN_CONSOLE_PARTNER_ID)
			return true;
			
		$regexps = vConf::get('user_login_password_structure');
		if($partnerId){
			$partner = PartnerPeer::retrieveByPK($partnerId);
			if($partner && $partner->getPasswordStructureRegex())
				$regexps = $partner->getPasswordStructureRegex();
		}
		if (!is_array($regexps)) {
			$regexps = array($regexps);
		}
		foreach($regexps as $regex) {
			if(!preg_match($regex, $pass)) {
				return false;
			}
		}	
		return true;
	}
			
	public static function decodePassHashKey($hashKey)
	{
		$decoded = base64_decode($hashKey);
		$params = explode('|', $decoded);
		if (count($params) != 3) {
			return false;
		}
		return $params;
	}
	
	public static function getIdFromHashKey($hashKey)
	{
		$params = self::decodePassHashKey($hashKey);
		if (isset($params[0])) {
			return $params[0];
		}
		return false;
	}
	
	public static function isHashKeyValid($hashKey)
	{
		// check hash key
		$id = self::getIdFromHashKey($hashKey);
		if (!$id) {
			throw new vUserException ('', vUserException::LOGIN_DATA_NOT_FOUND);
		}
		$loginData = self::retrieveByPK($id);
		if (!$loginData) {
			throw new vUserException ('', vUserException::LOGIN_DATA_NOT_FOUND);
		}
		
		// might throw an exception
		$valid = $loginData->isPassHashKeyValid($hashKey);
		
		if (!$valid) {
			throw new vUserException ('', vUserException::NEW_PASSWORD_HASH_KEY_INVALID);
		}

		return $loginData;
	}
	
	public static function setInitialPassword($hashKey, $newPassword)
	{
		// might throw exception
		$hashKey = str_replace('.','=', $hashKey);
		$loginData = self::isHashKeyValid($hashKey);
		
		if (!$loginData) {
			throw new vUserException ('', vUserException::NEW_PASSWORD_HASH_KEY_INVALID);
		}
		// check password structure
		if (!self::isPasswordStructureValid($newPassword, $loginData->getConfigPartnerId())   ||
			stripos($newPassword, $loginData->getFirstName()) !== false   ||
			stripos($newPassword, $loginData->getLastName()) !== false    ||
			$newPassword == $loginData->getLoginEmail()  ) {
			throw new vUserException ('', vUserException::PASSWORD_STRUCTURE_INVALID);
		}
		
		// check that password wasn't used before
		if ($loginData->passwordUsedBefore($newPassword)) {
			throw new vUserException ('', vUserException::PASSWORD_ALREADY_USED);
		}
		
		$loginData->resetPassword($newPassword);
		myPartnerUtils::initialPasswordSetForFreeTrial($loginData);

		return true;
	}
	
	public static function getPassResetLink($hashKey)
	{
		if (!$hashKey) {
			return null;
		}
		$loginData = self::isHashKeyValid($hashKey);
		if (!$loginData) {
			throw new Exception('Hash key not valid');
		}
		
		$resetLinksArray = vConf::get('password_reset_links');
		$resetLinkPrefix = $resetLinksArray['default'];		
		
		$partnerId = $loginData->getConfigPartnerId();
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if ($partner) {
			// partner may define a custom reset password url (admin console for example)
			$urlPrefixName = $partner->getPassResetUrlPrefixName();
			if ($urlPrefixName && isset($resetLinksArray[$urlPrefixName]))
			{
				$resetLinkPrefix = $resetLinksArray[$urlPrefixName];
			}
		}

		$httpsEnforcePermission = PermissionPeer::isValidForPartner(PermissionName::FEATURE_VMC_ENFORCE_HTTPS, $partnerId);
		if(strpos($resetLinkPrefix, infraRequestUtils::PROTOCOL_HTTPS) === false && $httpsEnforcePermission)
			$resetLinkPrefix = str_replace(infraRequestUtils::PROTOCOL_HTTP , infraRequestUtils::PROTOCOL_HTTPS , $resetLinkPrefix);

		return $resetLinkPrefix.$hashKey;
	}
	
	// user login by user_login_data record id
	public static function userLoginByDataId($loginDataId, $password, $partnerId = null)
	{
		$loginData = self::retrieveByPK($loginDataId);
		if (!$loginData) {
			throw new vUserException('', vUserException::LOGIN_DATA_NOT_FOUND);
		}
		
		return self::userLogin($loginData, $password, $partnerId, true);
	}
	
	// user login by login_email
	public static function userLoginByEmail($email, $password, $partnerId = null, $otp = null)
	{
		$loginData = self::getByEmail($email);
		if (!$loginData) {
			throw new vUserException('', vUserException::LOGIN_DATA_NOT_FOUND);
		}
		return self::userLogin($loginData, $password, $partnerId, true, $otp);
	}
	
	// user login by vs
	public static function userLoginByVs($vs, $requestedPartnerId, $useOwnerIfNoUser = false)
	{
		$vsObj = vSessionUtils::crackVs($vs);
		
		$vsUserId = $vsObj->user;
		$vsPartnerId = $vsObj->partner_id;
		$vuser = null;
		
		if ((is_null($vsUserId) || $vsUserId === '') && $useOwnerIfNoUser)
		{
			$partner = PartnerPeer::retrieveByPK($vsPartnerId);
			if (!$partner) {
				throw new vUserException('Invalid partner id ['.$vsPartnerId.']', vUserException::INVALID_PARTNER);
			}
			$vsUserId = $partner->getAccountOwnerVuserId();
			$vuser = vuserPeer::retrieveByPK($vsUserId);
		}
		
		if (!$vuser) {
			$vuser = vuserPeer::getVuserByPartnerAndUid($vsPartnerId, $vsUserId, true);
		}
		if (!$vuser)
		{
			throw new vUserException('User with id ['.$vsUserId.'] was not found for partner with id ['.$vsPartnerId.']', vUserException::USER_NOT_FOUND);
		}
			
		return self::userLogin($vuser->getLoginData(), null, $requestedPartnerId, false);  // don't validate password		
	}


	// user login by user_login_data object
	private static function userLogin(UserLoginData $loginData = null, $password, $partnerId = null, $validatePassword = true, $otp = null)
	{
		$requestedPartner = $partnerId;
		
		if (!$loginData) {
			throw new vUserException('', vUserException::LOGIN_DATA_NOT_FOUND);
		}		
		
		// check if password is valid
		if ($validatePassword && !$loginData->isPasswordValid($password)) 
		{
			if (time() < $loginData->getLoginBlockedUntil(null)) 
			{
				throw new vUserException('', vUserException::LOGIN_BLOCKED);
			}
			if ($loginData->getLoginAttempts()+1 >= $loginData->getMaxLoginAttempts()) 
			{
				$loginData->setLoginBlockedUntil( time() + ($loginData->getLoginBlockPeriod()) );
				$loginData->setLoginAttempts(0);
				$loginData->save();
				throw new vUserException('', vUserException::LOGIN_RETRIES_EXCEEDED);
			}
			$loginData->incLoginAttempts();
			$loginData->save();	
				
			throw new vUserException('', vUserException::WRONG_PASSWORD);
		}
		
		if (time() < $loginData->getLoginBlockedUntil(null)) {
			throw new vUserException('', vUserException::LOGIN_BLOCKED);
		}
		
		//Check if the user's ip address is in the right range to ignore the otp
		
		if(vConf::hasParam ('otp_required_partners') && 
			in_array ($partnerId, vConf::get ('otp_required_partners')) &&
			vConf::hasParam ('partner_otp_internal_ips'))
		{
			$otpRequired = true;
			$ipRanges = explode(',', vConf::get('partner_otp_internal_ips'));
			foreach ($ipRanges as $curRange)
			{
				if (vIpAddressUtils::isIpInRange(infraRequestUtils::getRemoteAddress(), $curRange))
				{
					$otpRequired = false;
					break;
				}
			}
			
			if ($otpRequired)
			{
				// add google authenticator library to include path
				require_once VIDIUN_ROOT_PATH . '/vendor/phpGangsta/GoogleAuthenticator.php';
				
				$result = GoogleAuthenticator::verifyCode ($loginData->getSeedFor2FactorAuth(), $otp);
				if (!$result)
				{
					throw new vUserException ('', vUserException::INVALID_OTP);
				}
			} 
		}
		
		$loginData->setLoginAttempts(0);
		$loginData->save();
		$passUpdatedAt = $loginData->getPasswordUpdatedAt(null);
		if ($passUpdatedAt && (time() > $passUpdatedAt + $loginData->getPassReplaceFreq())) {
			throw new vUserException('', vUserException::PASSWORD_EXPIRED);
		}
		if (!$partnerId) {
			$partnerId = $loginData->getLastLoginPartnerId();
		}
		if (!$partnerId) {
			throw new vUserException('', vUserException::INVALID_PARTNER);
		}
		
		$partner = PartnerPeer::retrieveByPK($partnerId);		
		$vuser = vuserPeer::getByLoginDataAndPartner($loginData->getId(), $partnerId);
		
		if (!$vuser || $vuser->getStatus() != VuserStatus::ACTIVE || !$partner || $partner->getStatus() != Partner::PARTNER_STATUS_ACTIVE)
		{
			// if a specific partner was requested - throw error
			if ($requestedPartner) {
				if ($partner && $partner->getStatus() != Partner::PARTNER_STATUS_ACTIVE) {
					throw new vUserException('Partner is blocked', vUserException::USER_IS_BLOCKED);
				}
				else if ($vuser && $vuser->getStatus() == VuserStatus::BLOCKED) {
					throw new vUserException('User is blocked', vUserException::USER_IS_BLOCKED);
				}
				else {
					throw new vUserException('', vUserException::USER_NOT_FOUND);
				}
			}
			
			// if vuser was found, keep status for following exception message
			$vuserStatus = $vuser ? $vuser->getStatus() : null;
			
			// if no specific partner was requested, but last logged in partner is not available, login to first found partner
			$vuser = null;
			$vuser = self::findFirstValidVuser($loginData->getId(), $partnerId);
			
			if (!$vuser) {
				if ($vuserStatus === VuserStatus::BLOCKED) {
					throw new vUserException('', vUserException::USER_IS_BLOCKED);
				}
				throw new vUserException('', vUserException::USER_NOT_FOUND);
			}
		}

		$userLoginEmailToIgnore =  vConf::getMap('UserLoginNoUpdate');
		$ignoreUser = isset ($userLoginEmailToIgnore[$loginData->getLoginEmail()]);
		$isAdmin = $vuser->getIsAdmin();
		$updateTimeLimit = $loginData->getUpdatedAt(null) + 5 < time();
		$ignorePartner = in_array($vuser->getPartnerId(), vConf::get('no_save_of_last_login_partner_for_partner_ids'));
		if ($isAdmin && !$ignoreUser && $updateTimeLimit && !$ignorePartner)
		{
			$loginData->setLastLoginPartnerId($vuser->getPartnerId());
		}
		$loginData->save();
		
		$currentTime = time();
		$dbLastLoginTime = $vuser->getLastLoginTime();
		if(!$ignoreUser && (!$dbLastLoginTime || $dbLastLoginTime < $currentTime - self::LAST_LOGIN_TIME_UPDATE_INTERVAL))
			$vuser->setLastLoginTime($currentTime);
		
		$vuser->save();
		
		return $vuser;
	}
	
	
	
	private static function findFirstValidVuser($loginDataId, $notPartnerId = null)
	{
		$c = new Criteria();
		$c->addAnd(vuserPeer::LOGIN_DATA_ID, $loginDataId);
		$c->addAnd(vuserPeer::STATUS, VuserStatus::ACTIVE, Criteria::EQUAL);
		$c->addAnd(vuserPeer::PARTNER_ID, PartnerPeer::GLOBAL_PARTNER, Criteria::GREATER_THAN);
		if ($notPartnerId) {
			$c->addAnd(vuserPeer::PARTNER_ID, $notPartnerId, Criteria::NOT_EQUAL);
		}
		$c->addAscendingOrderByColumn(vuserPeer::PARTNER_ID);
		
		$vusers = vuserPeer::doSelect($c);
						
		foreach ($vusers as $vuser)
		{
			if ($vuser->getStatus() != VuserStatus::ACTIVE)
			{
				continue;
			}
			$partner = PartnerPeer::retrieveByPK($vuser->getPartnerId());
			if (!$partner || $partner->getStatus() != Partner::PARTNER_STATUS_ACTIVE)
			{
				continue;
			}
			
			return $vuser;
		}
		
		return null;
	}
	
	/**
	 * Adds a new user login data record
	 * @param unknown_type $loginEmail
	 * @param unknown_type $password
	 * @param unknown_type $partnerId
	 * @param unknown_type $firstName
	 * @param unknown_type $lastName
	 * @param bool $checkPasswordStructure backward compatibility - some extensions are registering a partner and setting its first password without checking its structure
	 *
	 * @throws vUserException::INVALID_EMAIL
	 * @throws vUserException::INVALID_PARTNER
	 * @throws vUserException::PASSWORD_STRUCTURE_INVALID
	 * @throws vUserException::LOGIN_ID_ALREADY_USED
	 * @throws vUserException::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED
	 */
	public static function addLoginData($loginEmail, $password, $partnerId, $firstName, $lastName, $isAdminUser, $checkPasswordStructure = true, &$alreadyExisted = null)
	{
		if (!vString::isEmailString($loginEmail)) {
			throw new vUserException('', vUserException::INVALID_EMAIL);
		}
			
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if (!$partner) {
			throw new vUserException('', vUserException::INVALID_PARTNER);
		}
		
		if ($isAdminUser)
		{
			$userQuota = $partner->getAdminLoginUsersQuota();
			$adminLoginUsersNum = $partner->getAdminLoginUsersNumber();
			// check if login users quota exceeded - value -1 means unlimited
			if ($adminLoginUsersNum  && (is_null($userQuota) || ($userQuota != -1 && $userQuota <= $adminLoginUsersNum))) {
				throw new vUserException('', vUserException::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED);
			}
		}
		
		$existingData = self::getByEmail($loginEmail);
		if (!$existingData)
		{
			if ($checkPasswordStructure && 
				!UserLoginDataPeer::isPasswordStructureValid($password, $partnerId)) {
				throw new vUserException('', vUserException::PASSWORD_STRUCTURE_INVALID);
			}
			
			// create a new login data record
			$loginData = new UserLoginData();
			$loginData->setConfigPartnerId($partnerId);
			$loginData->setLoginEmail($loginEmail);
			$loginData->setFirstName($firstName);
			$loginData->setLastName($lastName);
			$loginData->setPassword($password);
			$loginData->setLoginAttempts(0);
			$loginData->setLoginBlockedUntil(null);
			$loginData->resetPreviousPasswords();
			$loginData->save();
			// now $loginData has an id and hash key can be generated
			$hashKey = $loginData->newPassHashKey();
			$loginData->setPasswordHashKey($hashKey);
			
			if ($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			{
				// add google authenticator library to include path
				require_once VIDIUN_ROOT_PATH . '/vendor/phpGangsta/GoogleAuthenticator.php';
				//generate a new secret for user's admin console logins
				$seed = GoogleAuthenticator::createSecret();
				$loginData->setSeedFor2FactorAuth($seed);
			}
			
			$loginData->save();
			$alreadyExisted = false;
			return $loginData;			
		}
		else
		{
			// add existing login data if password is valid
			$existingVuser = vuserPeer::getByLoginDataAndPartner($existingData->getId(), $partnerId);
			if ($existingVuser) {
				// partner already has a user with the same login data
				throw new vUserException('', vUserException::LOGIN_ID_ALREADY_USED);
			}
			
			if ($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			{
				// add google authenticator library to include path
				require_once VIDIUN_ROOT_PATH . '/vendor/phpGangsta/GoogleAuthenticator.php';
				//generate a new secret for user's admin console logins
				$existingData->setSeedFor2FactorAuth(GoogleAuthenticator::createSecret());
				$existingData->save();
			}
			
						
			VidiunLog::info('Existing login data with the same email & password exists - returning id ['.$existingData->getId().']');	
			$alreadyExisted = true;
			
			if ($isAdminUser && !$existingData->isLastLoginPartnerIdSet()) {
				$existingData->setLastLoginPartnerId($partnerId);
				$existingData->save();
			}
			
			return $existingData;
		}	
	}
	
	/**
	 * 
	 * updates first and last name on the login data record, according to the given vuser object
	 * @param int $loginDataId
	 * @param vuser $vuser
	 * @throws vUserException::LOGIN_DATA_NOT_FOUND
	 */
	public static function updateFromUserDetails($loginDataId, vuser $vuser)
	{
		$loginData = self::retrieveByPK($loginDataId);
		if (!$loginData) {
			throw new vUserException('', vUserException::LOGIN_DATA_NOT_FOUND);
		}
		
		$loginData->setFirstName($vuser->getFirstName());
		$loginData->setLastName($vuser->getLastName());
		$loginData->save();	
	}
	
	
	public static function notifyOneLessUser($loginDataId)
	{
		if (!$loginDataId) {
			return;
		}
		
		vuserPeer::setUseCriteriaFilter(false);
		$c = new Criteria();
		$c->addAnd(vuserPeer::PARTNER_ID, null, Criteria::NOT_EQUAL);
		$c->addAnd(vuserPeer::LOGIN_DATA_ID, $loginDataId);
		$c->addAnd(vuserPeer::STATUS, VuserStatus::DELETED, Criteria::NOT_EQUAL);
		$countUsers = vuserPeer::doCount($c);
		vuserPeer::setUseCriteriaFilter(true);
		
		if ($countUsers <= 0) {
			$loginData = self::retrieveByPK($loginDataId);
			if($loginData)
				$loginData->delete();
		}
		
		
	}
	
	/* (non-PHPdoc)
	 * @see IRelatedObjectPeer::getRootObjects()
	 */
	public function getRootObjects(IRelatedObject $object)
	{
		return array();
	}

	/* (non-PHPdoc)
	 * @see IRelatedObjectPeer::isReferenced()
	 */
	public function isReferenced(IRelatedObject $object)
	{
		return true;
	}
	public static function getCacheInvalidationKeys()
	{
		return array(array("userLoginData:id=%s", self::ID), array("userLoginData:loginEmail=%s", self::LOGIN_EMAIL));		
	}
} // UserLoginDataPeer

<?php
/**
 * Subclass for performing query and update operations on the 'vuser' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class vuserPeer extends BasevuserPeer implements IRelatedObjectPeer
{	
	const VIDIUN_NEW_USER_EMAIL = 120;
	const VIDIUN_NEW_EXISTING_USER_EMAIL = 121;
	const VIDIUN_NEW_USER_EMAIL_TO_ADMINS = 122;
	const VIDIUN_NEW_USER_ADMIN_CONSOLE_EMAIL = 123;
	const VIDIUN_NEW_EXISTING_USER_ADMIN_CONSOLE_EMAIL = 124;
	const VIDIUN_NEW_USER_ADMIN_CONSOLE_EMAIL_TO_ADMINS = 125;
	const MAX_PUSER_LENGTH = 100;

	private static $s_default_count_limit = 301;

	public static function setDefaultCriteriaFilter ()
	{
		if ( self::$s_criteria_filter == null )
		{
			self::$s_criteria_filter = new criteriaFilter ();
		}
		
		$c = VidiunCriteria::create(vuserPeer::OM_CLASS);
		$c->addAnd ( vuserPeer::STATUS, VuserStatus::DELETED, VidiunCriteria::NOT_EQUAL);
		self::$s_criteria_filter->setFilter ( $c );
	}
	
	public static function getVuserByScreenName( $screen_name  )
	{
		$c = new Criteria();
		$c->add ( vuserPeer::SCREEN_NAME , $screen_name );
		return self::doSelectOne( $c ); 
	}
	
	/**
	 * @param int $partnerId
	 * @param string $puserId
	 * @param bool $ignorePuserVuser
	 * @return vuser
	 */
	public static function getVuserByPartnerAndUid($partnerId, $puserId, $ignorePuserVuser = false)
	{
		$puserId = self::getValidPuserStr($puserId);

		if(!$ignorePuserVuser && !vCurrentContext::isApiV3Context())
		{
			$puserVuser = PuserVuserPeer::retrieveByPartnerAndUid($partnerId, 0, $puserId, true);
			if($puserVuser)
				return $puserVuser->getVuser();
		}
		
		$c = new Criteria();
		$c->add(self::PARTNER_ID, $partnerId);
		$c->add(self::PUSER_ID, $puserId);

		// in case of more than one deleted vusers - get the last one
		$c->addDescendingOrderByColumn(vuserPeer::UPDATED_AT);

		return self::doSelectOne($c);
	}

	private static function getValidPuserStr($puserId){
		if (!is_null($puserId))
			$puserId = substr($puserId, 0, self::MAX_PUSER_LENGTH);
		return $puserId;
	}
	
	/**
	 * @param int $partner_id
	 * @param array $puser_ids
	 * @return array<vuser>
	 */
	public static function getVuserByPartnerAndUids($partner_id, array $puser_ids)
	{
		$c = new Criteria();
		$c->add(self::PARTNER_ID, $partner_id);
		$c->add(self::PUSER_ID, $puser_ids, Criteria::IN);
		return self::doSelect($c);
	}
	
	public static function getActiveVuserByPartnerAndUid($partner_id , $puser_id)
	{
		if ($puser_id == '')
			return null;
			
		$c = new Criteria();
		$c->add(self::STATUS, VuserStatus::ACTIVE);
		$c->add(self::PARTNER_ID, $partner_id);
		$c->add(self::PUSER_ID, $puser_id);
		return self::doSelectOne($c);			
	}

	public static function createVuserForPartner($partner_id, $puser_id, $is_admin = false)
	{
		$puser_id = self::getValidPuserStr($puser_id);
		$vuser = vuserPeer::getVuserForPartner($partner_id, $puser_id);
		if(!$vuser)
		{
			$lockKey = "user_add_" . $partner_id . $puser_id;
			$vuser = vLock::runLocked($lockKey, array('vuserPeer', 'createUniqueVuserForPartner'), array($partner_id, $puser_id, $is_admin));
		}
		return $vuser;
	}

	public static function createUniqueVuserForPartner($partner_id, $puser_id, $is_admin = false)
	{
		$vuser = vuserPeer::getVuserForPartner($partner_id, $puser_id);
		if (!$vuser)
			return vuserPeer::createNewUser($partner_id, $puser_id, $is_admin);

		return $vuser;
	}
	
	public static function createNewUser($partner_id, $puser_id, $is_admin)
	{
		$vuser = new vuser();
		$vuser->setPuserId($puser_id);
		$vuser->setScreenName($puser_id);
		$vuser->setFirstName($puser_id);
		$vuser->setPartnerId($partner_id);
		$vuser->setStatus(VuserStatus::ACTIVE);
		$vuser->setIsAdmin($is_admin);
		$vuser->save();
		return $vuser;
	}

	/**
	 * Replaces 'getVuserByPartnerAndUid' and doesn't use its default conditions.
	 * @param string $partnerId
	 * @param string $puserId
	 */
	protected static function getVuserForPartner($partnerId, $puserId) {
		self::setUseCriteriaFilter(false);
		$c = new Criteria();
		$c->add(self::PARTNER_ID, $partnerId);
		$c->add(self::PUSER_ID, $puserId);
		$c->addAnd ( vuserPeer::STATUS, VuserStatus::DELETED, VidiunCriteria::NOT_EQUAL);
		
		$vuser = self::doSelectOne($c);
		self::setUseCriteriaFilter(true);
		return $vuser;
	}
	
	/**
	 * This function returns a pager object holding the given user's favorite users
	 *
	 * @param int $vuserId = the requested user
	 * @param int $privacy = the privacy filter
	 * @param int $pageSize = number of vshows in each page
	 * @param int $page = the requested page
	 * @return the pager object
	 */
	public static function getUserFavorites($vuserId, $privacy, $pageSize, $page)
	{
		$c = new Criteria();
		$c->addJoin(vuserPeer::ID, favoritePeer::SUBJECT_ID, Criteria::INNER_JOIN);
		$c->add(favoritePeer::VUSER_ID, $vuserId);
		$c->add(favoritePeer::SUBJECT_TYPE, favorite::SUBJECT_TYPE_USER);
		$c->add(favoritePeer::PRIVACY, $privacy);
		$c->setDistinct();
		
		// our assumption is that a request for private favorites should include public ones too 
		if( $privacy == favorite::PRIVACY_TYPE_USER ) 
		{
			$c->addOr( favoritePeer::PRIVACY, favorite::PRIVACY_TYPE_WORLD );
		}
			
		$c->addAscendingOrderByColumn(vuserPeer::SCREEN_NAME);
		
	    $pager = new sfPropelPager('vuser', $pageSize);
	    $pager->setCriteria($c);
	    $pager->setPage($page);
	    $pager->init();
			    
	    return $pager;
	}

	/**
	 * This function returns a pager object holding the given user's favorite entries
	 * each entry holds the vuser object of its host.
	 *
	 * @param int $vuserId = the requested user
	 * @param int $privacy = the privacy filter
	 * @param int $pageSize = number of vshows in each page
	 * @param int $page = the requested page
	 * @return the pager object
	 */
	public static function getUserFans($vuserId, $privacy, $pageSize, $page)
	{
		$c = new Criteria();
		$c->addJoin(vuserPeer::ID, favoritePeer::VUSER_ID, Criteria::INNER_JOIN);
		$c->add(favoritePeer::SUBJECT_ID, $vuserId);
		$c->add(favoritePeer::SUBJECT_TYPE, favorite::SUBJECT_TYPE_USER);
		$c->add(favoritePeer::PRIVACY, $privacy);
		
		$c->setDistinct();
		
		// our assumption is that a request for private favorites should include public ones too 
		if( $privacy == favorite::PRIVACY_TYPE_USER ) 
		{
			$c->addOr( favoritePeer::PRIVACY, favorite::PRIVACY_TYPE_WORLD );
		}
			
		$c->addAscendingOrderByColumn(vuserPeer::SCREEN_NAME);
		
	    $pager = new sfPropelPager('vuser', $pageSize);
	    $pager->setCriteria($c);
	    $pager->setPage($page);
	    $pager->init();
			    
	    return $pager;
	}
	
	/**
	 * This function returns a pager object holding the specified list of user favorites, 
	 * sorted by a given sort order.
	 * the $mine_flag param decides if to return favorite people or fans
	 */
	public static function getUserFavoritesOrderedPager( $order, $pageSize, $page, $vuserId, $mine_flag )
	{
		$c = new Criteria();
		
		if ( $mine_flag ) 
		{
			$c->addJoin(vuserPeer::ID, favoritePeer::SUBJECT_ID, Criteria::INNER_JOIN);
			$c->add(favoritePeer::VUSER_ID, $vuserId); 
		}
		else 
		{
			$c->addJoin(vuserPeer::ID, favoritePeer::VUSER_ID, Criteria::INNER_JOIN);
			$c->add(favoritePeer::SUBJECT_ID, $vuserId); 
		}
			
		$c->add(favoritePeer::SUBJECT_TYPE, favorite::SUBJECT_TYPE_USER);
		
		// TODO: take privacy into account
		$privacy = favorite::PRIVACY_TYPE_USER;
		$c->add(favoritePeer::PRIVACY, $privacy);
		// our assumption is that a request for private favorites should include public ones too 
		if( $privacy == favorite::PRIVACY_TYPE_USER ) 
		{
			$c->addOr( favoritePeer::PRIVACY, favorite::PRIVACY_TYPE_WORLD );
		}
			
		switch( $order )
		{
			
			case vuser::VUSER_SORT_MOST_VIEWED: $c->addDescendingOrderByColumn(vuserPeer::VIEWS);  break;
			case vuser::VUSER_SORT_MOST_RECENT: $c->addAscendingOrderByColumn(vuserPeer::CREATED_AT);  break;
			case vuser::VUSER_SORT_NAME: $c->addAscendingOrderByColumn(vuserPeer::SCREEN_NAME); break;
			case vuser::VUSER_SORT_AGE: $c->addAscendingOrderByColumn(vuserPeer::DATE_OF_BIRTH); break;
			case vuser::VUSER_SORT_COUNTRY: $c->addAscendingOrderByColumn(vuserPeer::COUNTRY); break;
			case vuser::VUSER_SORT_CITY: $c->addAscendingOrderByColumn(vuserPeer::CITY); break;
			case vuser::VUSER_SORT_GENDER: $c->addAscendingOrderByColumn(vuserPeer::GENDER); break;		
			case vuser::VUSER_SORT_PRODUCED_VSHOWS: $c->addDescendingOrderByColumn(vuserPeer::PRODUCED_VSHOWS); break;
			
			default: $c->addAscendingOrderByColumn(vuserPeer::SCREEN_NAME);
		}
		
		$c->setDistinct();
		
		
	    $pager = new sfPropelPager('vuser', $pageSize);
	    $pager->setCriteria($c);
	    $pager->setPage($page);
	    $pager->init();
			    
	    return $pager;
	}
	
	
	/**
	 * This function returns a pager object holding all the users
	 */
	public static function getAllUsersOrderedPager( $order, $pageSize, $page )
	{
		$c = new Criteria();
		
		switch( $order )
		{
			
			case vuser::VUSER_SORT_MOST_VIEWED: $c->addDescendingOrderByColumn(vuserPeer::VIEWS);  break;
			case vuser::VUSER_SORT_MOST_RECENT: $c->addAscendingOrderByColumn(vuserPeer::CREATED_AT);  break;
			case vuser::VUSER_SORT_NAME: $c->addAscendingOrderByColumn(vuserPeer::SCREEN_NAME); break;
			case vuser::VUSER_SORT_AGE: $c->addAscendingOrderByColumn(vuserPeer::DATE_OF_BIRTH); break;
			case vuser::VUSER_SORT_COUNTRY: $c->addAscendingOrderByColumn(vuserPeer::COUNTRY); break;
			case vuser::VUSER_SORT_CITY: $c->addAscendingOrderByColumn(vuserPeer::CITY); break;
			case vuser::VUSER_SORT_GENDER: $c->addAscendingOrderByColumn(vuserPeer::GENDER); break;		
			case vuser::VUSER_SORT_MOST_ENTRIES: $c->addDescendingOrderByColumn(vuserPeer::ENTRIES); break;		
			case vuser::VUSER_SORT_MOST_FANS: $c->addDescendingOrderByColumn(vuserPeer::FANS); break;		
			
			default: $c->addAscendingOrderByColumn(vuserPeer::SCREEN_NAME);
		}
		
		$pager = new sfPropelPager('vuser', $pageSize);
	    $pager->setCriteria($c);
	    $pager->setPage($page);
	    $pager->init();
			    
	    return $pager;
	}
	

	public static function selectIdsForCriteria ( Criteria $c )
	{
		$c->addSelectColumn(self::ID);
		$rs = self::doSelectStmt($c);
		$id_list = Array();
		
		while($rs->next())
		{
			$id_list[] = $rs->getInt(1);
		}
		
		$rs->close();
		
		return $id_list;
	}

	public static function doCountWithLimit (Criteria $criteria, $distinct = false, $con = null)
	{
		$criteria = clone $criteria;
		$criteria->clearSelectColumns()->clearOrderByColumns();
		if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->addSelectColumn("DISTINCT ".self::ID);
		} else {
			$criteria->addSelectColumn(self::ID);
		}

		$criteria->setLimit( self::$s_default_count_limit );
		
		$rs = self::doSelectStmt($criteria, $con);
		$count = 0;
		while($rs->next())
			$count++;
	
		return $count;
	}
	
	/**
	 * @param Criteria $criteria
	 * @param PropelPDO $con
	 */
	public static function doSelect(Criteria $criteria, PropelPDO $con = null)
	{
		$c = clone $criteria;
		
		if($c instanceof VidiunCriteria)
		{ 
			$c->applyFilters();
			$criteria->setRecordsCount($c->getRecordsCount());
		}

		return parent::doSelect($c, $con);
	}
	
	
	public static function doStubCount (Criteria $criteria, $distinct = false, $con = null)
	{
		return 0;
	}
	
	/**
	 * @param string $email
	 * @return vuser
	 */
	public static function getVuserByEmail($email, $partnerId = null)
	{
		$c = new Criteria();
		$c->add (vuserPeer::EMAIL, $email);
		
		if(!is_null($partnerId))
			$c->add (vuserPeer::PARTNER_ID, $partnerId);
			
		$vuser = vuserPeer::doSelectOne( $c );
		
		return $vuser;
		
	}
	
	/**
	 * @param int $id
	 * @return string
	 */
	public static function getEmailById($id)
	{
		$vuser = vuserPeer::retrieveByPK($id);
		return $vuser->getEmail();
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param int $partnerId
	 * @return vuser
	 */
	public static function userLogin($puserId, $password, $partnerId)
	{
		$vuser = self::getVuserByPartnerAndUid($partnerId , $puserId);
		if (!$vuser) {
			throw new vUserException('', vUserException::USER_NOT_FOUND);
		}

		if (!$vuser->getLoginDataId()) {
			throw new vUserException('', vUserException::LOGIN_DATA_NOT_FOUND);
		}
		
		$vuser = UserLoginDataPeer::userLoginByDataId($vuser->getLoginDataId(), $password, $partnerId);
					
		return $vuser;
	}
	
	
	public static function getByLoginDataAndPartner($loginDataId, $partnerId)
	{
		$c = new Criteria();
		$c->addAnd(vuserPeer::LOGIN_DATA_ID, $loginDataId);
		$c->addAnd(vuserPeer::PARTNER_ID, $partnerId);
		$c->addAnd(vuserPeer::STATUS, VuserStatus::DELETED, Criteria::NOT_EQUAL);
		$vuser = self::doSelectOne($c);
		if (!$vuser) {
			return false;
		}
		return $vuser;
	}
	
	
	/**
	 * Adds a new vuser and user_login_data records as needed
	 * @param vuser $user
	 * @param string $password
	 * @param bool $checkPasswordStructure
	 * @throws vUserException::USER_NOT_FOUND
	 * @throws vUserException::USER_ALREADY_EXISTS
	 * @throws vUserException::INVALID_EMAIL
	 * @throws vUserException::INVALID_PARTNER
	 * @throws vUserException::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED
	 * @throws vUserException::LOGIN_ID_ALREADY_USED
	 * @throws vUserException::PASSWORD_STRUCTURE_INVALID
	 * @throws vPermissionException::ROLE_ID_MISSING
	 * @throws vPermissionException::ONLY_ONE_ROLE_PER_USER_ALLOWED
	 */
	public static function addUser(vuser $user, $password = null, $checkPasswordStructure = true, $sendEmail = null)
	{
		if (!$user->getPuserId()) {
			throw new vUserException('', vUserException::USER_ID_MISSING);
		}
		
		// check if user with the same partner and puserId already exists		
		$existingUser = vuserPeer::getVuserByPartnerAndUid($user->getPartnerId(), $user->getPuserId());
		if ($existingUser) {
			throw new vUserException('', vUserException::USER_ALREADY_EXISTS);
		}
		
		// check if roles are valid - may throw exceptions
		if (!$user->getRoleIds() && $user->getIsAdmin()) {
			// assign default role according to user type admin / normal
			$userRoleId = $user->getPartner()->getAdminSessionRoleId();
			$user->setRoleIds($userRoleId);
		}
		UserRolePeer::testValidRolesForUser($user->getRoleIds(), $user->getPartnerId());
		
		if($user->getScreenName() === null) {
			$user->setScreenName($user->getPuserId());
		}
			
		if($user->getFullName() === null) {
			$user->setFirstName($user->getPuserId());
		}
		
		if (is_null($user->getStatus())) {
			$user->setStatus(VuserStatus::ACTIVE);
		}
		
		// if password is set, user should be able to login to the system - add a user_login_data record
		if ($password || $user->getIsAdmin()) {
			// throws an action on error
			$user->enableLogin($user->getEmail(), $password, $checkPasswordStructure, $sendEmail);
		}	
		
		$user->save();
		return $user;
	}
	
	
	
	public static function sendNewUserMailToAdmins(vuser $user)
	{
		$partnerId = $user->getPartnerId();
		$creatorUserName = 'Unknown';
		if (!is_null(vCurrentContext::$vs_uid))
		{
			$creatorUser = vuserPeer::getVuserByPartnerAndUid($partnerId, vCurrentContext::$vs_uid);
			if ($creatorUser) {
				$creatorUserName = $creatorUser->getFullName();
			}
		}
		$publisherName = PartnerPeer::retrieveByPK($partnerId)->getName();
		$loginEmail = $user->getEmail();
		$roleName = $user->getUserRoleNames();
		$puserId = $user->getPuserId();
		
		$bodyParams = null;


		$mailType = self::VIDIUN_NEW_USER_EMAIL_TO_ADMINS;
		
		//If the new user partner is -2 (admin console) then it is a admin console user		
		if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
		{
			$mailType = self::VIDIUN_NEW_USER_ADMIN_CONSOLE_EMAIL_TO_ADMINS;
		}
				
		// get all partner administrators
		$adminVusers = Partner::getAdminLoginUsersList($partnerId);
		foreach ($adminVusers as $admin)
		{
			// don't send mail to the created user
			if ($admin->getId() == $user->getId())
			{
				continue;
			}
			
			// send email to all administrators with user management permissions
			if ($admin->hasPermissionOr(array(PermissionName::ADMIN_USER_ADD, PermissionName::ADMIN_USER_UPDATE, PermissionName::ADMIN_USER_DELETE)))
			{
				$adminName = $admin->getFullName();
				if (!$adminName) { $adminName = $admin->getPuserId(); }
				$unsubscribeLink .= $admin->getEmail();
				$bodyParams = null;
				
				if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID) // Mail for admin console user
				{
					$bodyParams = array($adminName, $creatorUserName, $loginEmail, $roleName);
				}
				else
				{
					$bodyParams = array($adminName, $creatorUserName, $publisherName, $loginEmail, $publisherName, $roleName, $publisherName, $partnerId);
				}
				
				// add mail job
				vJobsManager::addMailJob(
					null, 
					0, 
					$partnerId, 
					$mailType, 
					vMailJobData::MAIL_PRIORITY_NORMAL, 
					vConf::get ("partner_registration_confirmation_email" ), 
					vConf::get ("partner_registration_confirmation_name" ), 
					$admin->getEmail(), 
					$bodyParams
				);
			}
		}
	}
	
	
	public static function sendNewUserMail(vuser $user, $existingUser)
	{
		// setup parameters
		$partnerId = $user->getPartnerId();
		$userName = $user->getFullName();
		if (!$userName) { $userName = $user->getPuserId(); }
		$creatorUserName = 'Unknown';
		if (!is_null(vCurrentContext::$vs_uid))
		{
			$creatorUser = vuserPeer::getVuserByPartnerAndUid($partnerId, vCurrentContext::$vs_uid);
			if ($creatorUser) {
				$creatorUserName = $creatorUser->getFullName();
			}
		}
		$publisherName = PartnerPeer::retrieveByPK($partnerId)->getName();
		$loginEmail = $user->getEmail();
		$roleName = $user->getUserRoleNames();
		$puserId = $user->getPuserId();
		if (!$existingUser) {
			$resetPasswordLink = UserLoginDataPeer::getPassResetLink($user->getLoginData()->getPasswordHashKey());
		}
		$vmcLink = trim(vConf::get('apphome_url'), '/').'/vmc';
		$adminConsoleLink = trim(vConf::get('admin_console_url'));
		$contactLink = vConf::get('contact_url');
		$beginnersGuideLink = vConf::get('beginners_tutorial_url');
		$quickStartGuideLink = vConf::get('quick_start_guide_url');
		
		// setup mail
		$mailType = null;
		$bodyParams = array();
		
		if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID) // If new user is admin console user
		{
			// add google authenticator library to include path
			require_once VIDIUN_ROOT_PATH . '/vendor/phpGangsta/GoogleAuthenticator.php';
			
			//QR code link might contain the '|' character used as a separator by the mailer job dispatcher. 
			$qrCodeLink = str_replace ("|", "M%7C", GoogleAuthenticator::getQRCodeGoogleUrl ($user->getPuserId() . ' ' . vConf::get ('www_host') . ' VAC', $user->getLoginData()->getSeedFor2FactorAuth()));
			
			if ($existingUser)
			{
				$mailType = self::VIDIUN_NEW_EXISTING_USER_ADMIN_CONSOLE_EMAIL;
				$bodyParams = array($userName, $creatorUserName, $loginEmail, $roleName, $qrCodeLink);
			}
			else
			{
				$mailType = self::VIDIUN_NEW_USER_ADMIN_CONSOLE_EMAIL;
				$bodyParams = array($userName, $creatorUserName, $loginEmail, $resetPasswordLink, $roleName, $adminConsoleLink, $qrCodeLink);
			}
		}
		else // Not an admin console partner
		{
			if ($existingUser)
			{
				$mailType = self::VIDIUN_NEW_EXISTING_USER_EMAIL;
				$bodyParams = array($userName, $creatorUserName, $publisherName, $loginEmail, $partnerId, $publisherName, $publisherName, $roleName, $publisherName, $puserId, $vmcLink, $contactLink, $beginnersGuideLink, $quickStartGuideLink);
			}
			else
			{
				$mailType = self::VIDIUN_NEW_USER_EMAIL;
				$bodyParams = array($userName, $creatorUserName, $publisherName, $loginEmail, $resetPasswordLink, $partnerId, $publisherName, $publisherName, $roleName, $publisherName, $puserId, $vmcLink, $contactLink, $beginnersGuideLink, $quickStartGuideLink);
			}		
		}
		// add mail job
		vJobsManager::addMailJob(
			null, 
			0, 
			$partnerId, 
			$mailType, 
			vMailJobData::MAIL_PRIORITY_NORMAL, 
			vConf::get ("partner_registration_confirmation_email" ), 
			vConf::get ("partner_registration_confirmation_name" ), 
			$loginEmail, 
			$bodyParams
		);
	}
			
	public static function getCacheInvalidationKeys()
	{
		return array(array("vuser:id=%s", self::ID), array("vuser:partnerId=%s,puserid=%s", self::PARTNER_ID, self::PUSER_ID), array("vuser:loginDataId=%s", self::LOGIN_DATA_ID));		
	}
	
	public static function retrieveByPKNoFilter($pk, PropelPDO $con = null)
	{
		self::setUseCriteriaFilter(false);
		$ret = self::retrieveByPK($pk, $con);
		self::setUseCriteriaFilter(true);
		return $ret;
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
}

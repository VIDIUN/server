<?php
class myPartnerRegistration
{
	private $partnerParentId = null;

	public function __construct( $partnerParentId = null )
	{
	    set_time_limit(vConf::get('partner_registration_timeout'));
		$this->partnerParentId = $partnerParentId;	
	}
	
	const VIDIUN_SUPPORT = "wikisupport@vidiun.com";
	  
	private function str_makerand ($minlength, $maxlength, $useupper, $usespecial, $usenumbers)
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

	const VIDIUNS_CMS_REGISTRATION_CONFIRMATION = 50;
	const VIDIUNS_DEFAULT_REGISTRATION_CONFIRMATION = 54;
	const VIDIUNS_EXISTING_USER_REGISTRATION_CONFIRMATION = 55;
	const VIDIUNS_DEFAULT_EXISTING_USER_REGISTRATION_CONFIRMATION = 56;
	const VIDIUNS_BLACKBOARD_DEFAULT_REGISTRATION_CONFIRMATION = 57;
	const VIDIUNS_DEVELOPER_REGISTRATION_CONFIRMATION = 220;
	const VIDIUNS_DEVELOPER_EXISTING_USER_REGISTRATION_CONFIRMATION = 221;
	
	public function sendRegistrationInformationForPartner ($partner, $skip_emails, $existingUser, $silent = false )
	{
		// email the client with this info
		$adminVuser = vuserPeer::retrieveByPK($partner->getAccountOwnerVuserId());
		if(!$silent)
		{
			$this->sendRegistrationInformation($partner, $adminVuser, $existingUser, null, $partner->getType());
		}
											
		if ( !$skip_emails && vConf::hasParam("report_partner_registration") && vConf::get("report_partner_registration")) 
		{											
			// email the wikisupport@vidiun.com  with this info
			$this->sendRegistrationInformation($partner, $adminVuser, $existingUser, self::VIDIUN_SUPPORT );

			// if need to hook into SalesForce - this is the place
			if ( include_once ( "mySalesForceUtils.class.php" ) )
			{
				mySalesForceUtils::sendRegistrationInformationToSalesforce($partner);
			}
			
			// if need to hook into Marketo - this is the place
			if ( include_once ( "myMarketoUtils.class.php" ) )
			{
				myMarketoUtils::sendRegistrationInformation($partner);
			}
		}
	}
	


	private  function sendRegistrationInformation(Partner $partner, vuser $adminVuser, $existingUser, $recipient_email = null , $partner_type = 1 )
	{
		$mailType = null;
		$bodyParams = array();
		$partnerId = $partner->getId();
		$userName = $adminVuser->getFullName();
		if (!$userName) { $userName = $adminVuser->getPuserId(); }
		$loginEmail = $adminVuser->getEmail();
		$loginData = $adminVuser->getLoginData();
		$hashKey = $loginData->getNewHashKeyIfCurrentInvalid();
		$resetPasswordLink = UserLoginDataPeer::getPassResetLink($hashKey);
		$vmcLink = trim(vConf::get('apphome_url'), '/').'/vmc';
		$contactLink = vConf::get('contact_url');
		$contactPhone = vConf::get('contact_phone_number');		
		$beginnersGuideLink = vConf::get('beginners_tutorial_url');
		$quickStartGuideLink = vConf::get('quick_start_guide_url');
		$uploadMediaVideoLink = vConf::get('upload_media_video_url');
		$howToPublishVideoLink = vConf::get('how_to_publish_video_url');
		$freeTrialResourceLink = vConf::get('free_trial_resource_url', 'local', '');
		if ( $recipient_email == null ) $recipient_email = $loginEmail;

		
	 	// send the $cms_email,$cms_password, TWICE !
	 	if(vConf::get('vidiun_installation_type') == 'CE')	{
			$partner_type = 1;
		}

		$avoidRegistrationArray = array (PartnerPackages::PARTNER_PACKAGE_DEVELOPER_TRIAL,PartnerPackages::PARTNER_PACKAGE_DEVELOPER_COMMIT);
		if(in_array($partner->getPartnerPackage(),$avoidRegistrationArray))
		{
			if ($existingUser) {
				return; // emails will be sent via external system 
				//$mailType = self::VIDIUNS_DEVELOPER_EXISTING_USER_REGISTRATION_CONFIRMATION;
				//$bodyParams = array($loginEmail, $partnerId);
			}
			else {
				return; // emails will be sent via external system
				//$mailType = self::VIDIUNS_DEVELOPER_REGISTRATION_CONFIRMATION;
				//$bodyParams = array($resetPasswordLink, $resetPasswordLink);
			}
		}
		else {
			switch($partner_type) { // send different email for different partner types
				case Partner::PARTNER_TYPE_VMC: // VMC signup
					if ($existingUser) {
						$mailType = self::VIDIUNS_DEFAULT_EXISTING_USER_REGISTRATION_CONFIRMATION;
						$bodyParams = array($userName, $loginEmail, $partnerId, $contactLink, $contactPhone, $freeTrialResourceLink);
					}
					else {
						$mailType = self::VIDIUNS_CMS_REGISTRATION_CONFIRMATION;
						$bodyParams = array($userName, $loginEmail, $resetPasswordLink, $partnerId, $vmcLink, $quickStartGuideLink, $uploadMediaVideoLink, $howToPublishVideoLink, $contactLink, $contactPhone);
					}
					break;
				//blackboard
				case Partner::PARTNER_TYPE_BLACKBOARD:
					if ($existingUser) {
						$mailType = self::VIDIUNS_DEFAULT_EXISTING_USER_REGISTRATION_CONFIRMATION;
						$bodyParams = array($userName, $loginEmail, $partnerId, $contactLink, $contactPhone, $freeTrialResourceLink);
					}
					else {
						$mailType = self::VIDIUNS_BLACKBOARD_DEFAULT_REGISTRATION_CONFIRMATION;
						$bodyParams = array($resetPasswordLink, $loginEmail, $partnerId, $vmcLink);
					}
					break;	
				default: // all others
				 	if ($existingUser) {
						$mailType = self::VIDIUNS_DEFAULT_EXISTING_USER_REGISTRATION_CONFIRMATION;
						$bodyParams = array($userName, $loginEmail, $partnerId, $contactLink, $contactPhone, $freeTrialResourceLink);
					}
					else {
						$mailType = self::VIDIUNS_DEFAULT_REGISTRATION_CONFIRMATION;
						$bodyParams = array($userName, $loginEmail, $partnerId, $resetPasswordLink, $vmcLink, $contactLink, $contactPhone, $beginnersGuideLink, $quickStartGuideLink);
					}
					break;
			}
		}
		
		vJobsManager::addMailJob(
			null, 
			0, 
			$partnerId, 
			$mailType, 
			vMailJobData::MAIL_PRIORITY_NORMAL, 
			vConf::get ("partner_registration_confirmation_email" ), 
			vConf::get ("partner_registration_confirmation_name" ), 
			$recipient_email, 
			$bodyParams);
	}

	/**
	 * Function creates new partner, saves all the required data to it, and copies objects & filesyncs of template content to its ID.
	 * @param string $partner_name
	 * @param string $contact
	 * @param string $email
	 * @param CommercialUseType $ID_is_for
	 * @param string $SDK_terms_agreement
	 * @param string $description
	 * @param string $website_url
	 * @param string $password
	 * @param Partner $partner
	 * @param int $templatePartnerId
	 * @return Partner
	 */
	private function createNewPartner( $partner_name , $contact, $email, $ID_is_for, $SDK_terms_agreement, $description, $website_url , $password = null , $newPartner = null, $templatePartnerId = null )
	{
		$secret = md5(VCryptoWrapper::random_pseudo_bytes(16));
		$admin_secret = md5(VCryptoWrapper::random_pseudo_bytes(16));

		if (!$newPartner)
			$newPartner = new Partner();

		if ($partner_name)
			$newPartner->setPartnerName($partner_name);
		$newPartner->setAdminSecret($admin_secret);
		$newPartner->setSecret($secret);
		$newPartner->setAdminName($contact);
		$newPartner->setAdminEmail($email);
		$newPartner->setUrl1($website_url);
		if ($ID_is_for === "commercial_use" || $ID_is_for === CommercialUseType::COMMERCIAL_USE)
			$newPartner->setCommercialUse(true);
		else //($ID_is_for == "non-commercial_use") || $ID_is_for === CommercialUseType::NON_COMMERCIAL_USE)
			$newPartner->setCommercialUse(false);
		$newPartner->setDescription($description);
		$newPartner->setVsMaxExpiryInSeconds(86400);
		$newPartner->setModerateContent(false);
		$newPartner->setNotify(false);
		$newPartner->setAppearInSearch(mySearchUtils::DISPLAY_IN_SEARCH_PARTNER_ONLY);
		$newPartner->setIsFirstLogin(true);
		/* fix drupal5 module partner type */
		//var_dump($description);
		
		if ( $this->partnerParentId )
		{
			// this is a child partner of some VAR/partner GROUP
			$newPartner->setPartnerParentId( $this->partnerParentId );
			$newPartner->setMonitorUsage(PartnerFreeTrialType::NO_LIMIT);
			$parentPartner = PartnerPeer::retrieveByPK($this->partnerParentId);
			$newPartner->setPartnerPackage($parentPartner->getPartnerPackage());
			$newPartner->setCrmId($parentPartner->getCrmId());
			$newPartner->setCrmLink($parentPartner->getCrmLink());
			$newPartner->setVerticalClasiffication($parentPartner->getVerticalClasiffication());
		}
		
		if(substr_count($description, 'Drupal module|'))
		{
			$newPartner->setType(102);
		}

		$newPartner->save();

		// if name was left empty - which should not happen - use id as name
		if ( ! $partner_name ) $partner_name = $newPartner->getId();
		$newPartner->setPartnerName( $partner_name );
		$newPartner->setPrefix($newPartner->getId());
		$newPartner->setPartnerAlias(md5($newPartner->getId().'vidiun partner'));

		// set default conversion profile for trial accounts
		if ($newPartner->getType() == Partner::PARTNER_TYPE_VMC)
		{
			$newPartner->setDefConversionProfileType( ConversionProfile::DEFAULT_TRIAL_COVERSION_PROFILE_TYPE );
		}
		
		$newPartner->save();
		
		// remove the default criteria from all peers and recreate it with the right partner id 
		myPartnerUtils::resetAllFilters();
		myPartnerUtils::applyPartnerFilters($newPartner->getId(), true);

		$partner_id = $newPartner->getId();
		widget::createDefaultWidgetForPartner( $partner_id , $this->createNewSubPartner ( $newPartner ) );
		
		$fromPartner = PartnerPeer::retrieveByPK($templatePartnerId ? $templatePartnerId : vConf::get("template_partner_id"));
	 	if (!$fromPartner)
	 		VidiunLog::log("Template content partner was not found!");
 		else
 		{
 			$newPartner->setI18nTemplatePartnerId($templatePartnerId);
	 		myPartnerUtils::copyTemplateContent($fromPartner, $newPartner, true);
 		}
	 		
	 	if ($newPartner->getType() == Partner::PARTNER_TYPE_WORDPRESS)
	 		vPermissionManager::setPs2Permission($newPartner);
		
		$newPartner->setVmcVersion(vConf::get('new_partner_vmc_version'));
		$newPartner->save();
		
		return $newPartner;
	}

	private function createNewSubPartner($newPartner)
	{
		$pid = $newPartner->getId();
		$subpid = ($pid*100);

		// TODO: save this, when implementation is ready

		return $subpid;
	}

	// if the adminVuser already exists - use his password - it should always be the same one for a given email !!
	private function createNewAdminVuser($newPartner , $existing_password )
	{		
		// generate a new password if not given
		if ( $existing_password != null ) {
			$password = $existing_password;
		}
		else {
			$password = UserLoginDataPeer::generateNewPassword();
		}
		
		// create the user
		$vuser = new vuser();
		$vuser->setEmail($newPartner->getAdminEmail());
		
		list($firstName, $lastName) = vString::nameSplit($newPartner->getAdminName());
		$vuser->setFirstName($firstName);
		$vuser->setLastName($lastName);

		$vuser->setPartnerId($newPartner->getId());
		$vuser->setIsAdmin(true);
		$vuser->setPuserId($newPartner->getAdminEmail());

		$vuser = vuserPeer::addUser($vuser, $password, false, false); //this also saves the vuser and adds a user_login_data record
		
		$loginData = UserLoginDataPeer::retrieveByPK($vuser->getLoginDataId());
	
		return array($password, $loginData->getPasswordHashKey(), $vuser->getId());
	}

	public function initNewPartner($partner_name , $contact, $email, $ID_is_for, $SDK_terms_agreement, $description, $website_url , $password = null , $partner = null, $ignorePassword = false, $templatePartnerId = null)
	{
		// Validate input fields
		if( $partner_name == "" )
			throw new SignupException("Please fill in the Partner's name" , SignupException::INVALID_FIELD_VALUE);
			
		if ($contact == "")
			throw new SignupException('Please fill in Administrator\'s details', SignupException::INVALID_FIELD_VALUE);

		if ($email == "")
			throw new SignupException('Please fill in Administrator\'s Email Address', SignupException::INVALID_FIELD_VALUE);
		
			
		if(!vString::isEmailString($email))
			throw new SignupException('Invalid email address', SignupException::INVALID_FIELD_VALUE);

		if ($description == "")
			throw new SignupException('Please fill in description', SignupException::INVALID_FIELD_VALUE);

		if ( ($ID_is_for !== CommercialUseType::COMMERCIAL_USE) && ($ID_is_for !== CommercialUseType::NON_COMMERCIAL_USE) &&
			 ($ID_is_for !== "commercial_use") && ($ID_is_for !== "non-commercial_use") ) //string values left for backward compatibility
			throw new SignupException('Invalid field value.\nSorry.', SignupException::UNKNOWN_ERROR);

		if ($SDK_terms_agreement != "yes")
			throw new SignupException('You haven`t approved Terms & Conds.', SignupException::INVALID_FIELD_VALUE);


		if ($partner->getPartnerPackage() == PartnerPackages::PARTNER_PACKAGE_INTERNAL_TRIAL)
		{
			$internalTrialAllowedDomains = vConf::get('internal_trial_allowed_domains', 'local', null);
			if($internalTrialAllowedDomains)
			{
				$domain = substr(strrchr($email, "@"), 1);
				if(!in_array($domain, $internalTrialAllowedDomains))
					throw new SignupException('The email domain is not allowed', SignupException::INVALID_FIELD_VALUE);
			}
		}

		$existingLoginData = UserLoginDataPeer::getByEmail($email);
		if ($existingLoginData && !$ignorePassword)
		{
			// if a another user already existing with the same adminEmail, new account will be created only if the right password was given
			$existingPartner = partnerPeer::retrieveByPK($existingLoginData->getConfigPartnerId());
			if (!$password)
			{
				$this->addMarketoCampaignId($existingPartner, myPartnerUtils::MARKETO_MISSING_PASSWORD, $partner);
				throw new SignupException("User with email [$email] already exists in system.", SignupException::MISSING_PASSWORD_FOR_EXISTING_EMAIL );
			}
			else if ($existingLoginData->isPasswordValid($password))
			{
				VidiunLog::log('Login id ['.$email.'] already used, and given password is valid. Creating new partner with this same login id');
			}
			else
			{
				$this->addMarketoCampaignId($existingPartner, myPartnerUtils::MARKETO_WRONG_PASSWORD, $partner);
				throw new SignupException("Invalid password for user with email [$email].", SignupException::INCORRECT_PASSWORD_FOR_EXISTING_EMAIL );
			}
			
			// for now allow multiple accounts using the same user
			//$this->allowOnlyOneActiveFreeTrialAccountCreation($partner, $email);
		}


			
		// TODO: log request
		$newPartner = NULL;
		$newSubPartner = NULL;
		try {
		    //validate that the template partner object counts do not exceed the limits stated in the local.ini
		    $templatePartner = PartnerPeer::retrieveByPK($templatePartnerId ? $templatePartnerId : vConf::get('template_partner_id'));
		    $this->validateTemplatePartner($templatePartner);
			// create the new partner
			$newPartner = $this->createNewPartner($partner_name , $contact, $email, $ID_is_for, $SDK_terms_agreement, $description, $website_url , $password , $partner , $templatePartnerId);

		    // create the sub partner
			// TODO: when ready, add here the saving of this value, currently it will be only
			// a random value, being passed to the user, and never saved
			$newSubPartnerId = $this->createNewSubPartner($newPartner);

			// create a new admin_vuser for the user,
			// so he will be able to login to the system (including permissions)
			list($newAdminVuserPassword, $newPassHashKey, $vuserId) = $this->createNewAdminVuser($newPartner , $password );
			$newPartner->setAccountOwnerVuserId($vuserId);
			$newPartner->save();
			
			$this->configurePartnerByPackage($newPartner);
					
			$this->setAllTemplateEntriesToAdminVuser($newPartner->getId(), $vuserId);

			if ($partner->getPartnerPackage() == PartnerPackages::PARTNER_PACKAGE_INTERNAL_TRIAL)
				$this->addMarketoCampaignId($newPartner, myPartnerUtils::MARKETO_NEW_INTERNAL_TRIAL_ACCOUNT, $newPartner);
			else
			{
				if(!$existingLoginData)
					$this->addMarketoCampaignId($newPartner, myPartnerUtils::MARKETO_NEW_TRIAL_ACCOUNT, $newPartner);

				if ($existingLoginData && !$ignorePassword)
					$this->addMarketoCampaignId($newPartner, myPartnerUtils::MARKETO_NEW_ADDITIONAL_TRIAL_ACCOUNT, $newPartner);

			}

			vEventsManager::raiseEvent(new vObjectAddedEvent($newPartner));

			return array($newPartner->getId(), $newSubPartnerId, $newAdminVuserPassword, $newPassHashKey);
		}
		catch (Exception $e) {
			//TODO: revert all changes, depending where and why we failed

			throw $e;
		}
	}

	private function addMarketoCampaignId($partnerToUpdate, $campaignName, $partnerToCheck)
	{
		//if an additional account was register we want to check if the additional is free trial and update the existing lead
		$additionalParams = $partnerToCheck->getAdditionalParams();
		$additionalParams = array_change_key_case($additionalParams);
		$freeTrialPackages = array(PartnerPackages::PARTNER_PACKAGE_FREE, PartnerPackages::PARTNER_PACKAGE_INTERNAL_TRIAL);
		if(in_array($partnerToCheck->getPartnerPackage(), $freeTrialPackages) && isset($additionalParams['freetrialaccounttype']))
		{
			if (vConf::hasParam($campaignName))
			{
				$campaignId = vConf::get($campaignName);
				$partnerToUpdate->setMarketoCampaignId($campaignId);
				$partnerToUpdate->save();
			}
		}
	}

	private function allowOnlyOneActiveFreeTrialAccountCreation($partner, $email)
	{
		$partnerPackage = $partner->getPartnerPackage();
		if ($this->partnerParentId)
		{
			$parentPartner = PartnerPeer::retrieveByPK($this->partnerParentId);
			$partnerPackage = $parentPartner->getPartnerPackage();
		}

		if($partnerPackage == PartnerPackages::PARTNER_PACKAGE_FREE)
		{
			$existingPartner = myPartnerUtils::retrieveNotDeletedPartnerByEmailAndPackage ($partner, PartnerPackages::PARTNER_PACKAGE_FREE);
			if($existingPartner)
			{
				$existingPartner->setAdditionalAccountFailureReason(SignupException::TRIAL_ACCOUNT_ALREADY_EXIST_FOR_EMAIL);
				$existingPartner->save();
				throw new SignupException("Free Trial user with email [$email] already exists in system.", SignupException::TRIAL_ACCOUNT_ALREADY_EXIST_FOR_EMAIL);
			}

		}
	}


	private function configurePartnerByPackage($partner)
	{
		if(!$partner)
			return;
			
		if($partner->getPartnerPackage() == 100) //Developer partner
		{
			$permissionNames = array(PermissionName::FEATURE_LIVE_STREAM, PermissionName::FEATURE_VIDIUN_LIVE_STREAM, PermissionName::FEATURE_VIDIUN_LIVE_STREAM_TRANSCODE);
			foreach ($permissionNames as $permissionName) 
			{
				$permission = PermissionPeer::getByNameAndPartner ( $permissionName, $partner->getId() );
				if (!$permission) {
		
					$permission = new Permission ();
					$permission->setType ( PermissionType::SPECIAL_FEATURE );
					$permission->setPartnerId (  $partner->getId());
					$permission->setName($permissionName);
				}
			
				$permission->setStatus ( PermissionStatus::ACTIVE );
				$permission->save ();			
			}
		}
	}
	
	/**
	 * Validate the amount of core and plugin objects found on the template partner.
	 * @param Partner $templatePartner
	 */
	private function validateTemplatePartner (Partner $templatePartner)
	{
	    //access control profiles
	    $c = new Criteria();
 		$c->add(accessControlPeer::PARTNER_ID, $templatePartner->getId());
 		$count = accessControlPeer::doCount($c);
 		
        if ($count > vConf::get('copy_partner_limit_ac_profiles'))
        {
            throw new vCoreException("Template partner's number of [accessControlProfiles] objects exceed allowed limit", vCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
        }
        
        //categories
        categoryPeer::setUseCriteriaFilter(false);
 		$c = new Criteria();
 		$c->addAnd(categoryPeer::PARTNER_ID, $templatePartner->getId());
 		$c->addAnd(categoryPeer::STATUS, CategoryStatus::ACTIVE);
 		$count = categoryPeer::doCount($c);
 	    if ($count > vConf::get('copy_partner_limit_categories'))
        {
            throw new vCoreException("Template partner's number of [category] objects exceed allowed limit", vCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
        }
        
 		categoryPeer::setUseCriteriaFilter(true);
 		
 		//conversion profiles
	    $c = new Criteria();
 		$c->add(conversionProfile2Peer::PARTNER_ID, $templatePartner->getId());
 		$count = conversionProfile2Peer::doCount($c);
 		if ($count > vConf::get('copy_partner_limit_conversion_profiles'))
 		{
 		    throw new vCoreException("Template partner's number of [conversionProfile] objects exceeds allowed limit", vCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		//entries
 		entryPeer::setUseCriteriaFilter ( false ); 
 		$c = new Criteria();
 		$c->addAnd(entryPeer::PARTNER_ID, $templatePartner->getId());
 		$c->addAnd(entryPeer::TYPE, entryType::MEDIA_CLIP);
 		$c->addAnd(entryPeer::STATUS, entryStatus::READY);
 		$count = entryPeer::doCount($c);
 		if ($count > vConf::get('copy_partner_limit_entries'))
 		{
 		    throw new vCoreException("Template partner's number of MEDIA_CLIP objects exceed allowed limit", vCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		entryPeer::setUseCriteriaFilter ( true );
 		
 		//playlists
		entryPeer::setUseCriteriaFilter ( false );
 		$c = new Criteria();
 		$c->addAnd(entryPeer::PARTNER_ID, $templatePartner->getId());
 		$c->addAnd(entryPeer::TYPE, entryType::PLAYLIST);
 		$c->addAnd(entryPeer::STATUS, entryStatus::READY);
 		$count = entryPeer::doCount($c);
 		if ($count > vConf::get('copy_partner_limit_playlists'))
 		{
 		    throw new vCoreException("Template partner's number of PLAYLIST objects exceed allowed limit", vCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		
 		entryPeer::setUseCriteriaFilter ( true );
 		
 		//flavor params
	    $c = new Criteria();
 		$c->add(assetParamsPeer::PARTNER_ID, $templatePartner->getId());
 		$count = assetParamsPeer::doCount($c);
 		if ($count > vConf::get('copy_partner_limit_flavor_params'))
 		{
 		    throw new vCoreException("Template partner's number of [flavorParams] objects exceeds allowed limit", vCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		
 		//uiconfs
 		uiConfPeer::setUseCriteriaFilter(false);
 		$c = new Criteria();
 		$c->addAnd(uiConfPeer::PARTNER_ID, $templatePartner->getId());
 		$c->addAnd(uiConfPeer::OBJ_TYPE, array (uiConf::UI_CONF_TYPE_VDP3, uiConf::UI_CONF_TYPE_WIDGET), Criteria::IN);
 		$c->addAnd(uiConfPeer::STATUS, uiConf::UI_CONF_STATUS_READY);
 		$count = uiConfPeer::doCount($c);
 		if ($count > vConf::get('copy_partner_limit_ui_confs'))
 		{
 		    throw new vCoreException("Template partner's number of [uiconf] objects exceeds allowed limit", vCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		uiConfPeer::setUseCriteriaFilter ( true );
 		
 		//user roles
 		UserRolePeer::setUseCriteriaFilter ( false );
 		$c = new Criteria();
 		$c->addAnd(UserRolePeer::PARTNER_ID, $templatePartner->getId(), Criteria::EQUAL);
 		$c->addAnd(UserRolePeer::STATUS, UserRoleStatus::ACTIVE, Criteria::EQUAL);
 		$count = UserRolePeer::doCount($c);
 		if ($count > vConf::get('copy_partner_limit_user_roles'))
 		{
 		    throw new vCoreException("Template partner's number of [userRole] objects exceed allowed limit", vCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		UserRolePeer::setUseCriteriaFilter ( true );
 		
 		$validatorPlugins = VidiunPluginManager::getPluginInstances('IVidiunObjectValidator');
 		foreach ($validatorPlugins as $validatorPlugins)
 		{
 		    $validatorPlugins->validateObject ($templatePartner, IVidiunObjectValidator::OPERATION_COPY);
 		}
        
	}
	
	private function setAllTemplateEntriesToAdminVuser($partnerId, $vuserId)
	{
		$c = new Criteria();
		$c->addAnd(entryPeer::PARTNER_ID, $partnerId, Criteria::EQUAL);
		entryPeer::setUseCriteriaFilter(false);
		$allEntries = entryPeer::doSelect($c);
		entryPeer::setUseCriteriaFilter(true);
			
		foreach ($allEntries as $entry)
		{
			$entry->setVuserId($vuserId);
			$entry->setCreatorVuserId($vuserId);
			$entry->save();
		}
	}
}


class SignupException extends Exception
{
    const UNKNOWN_ERROR = 500;
    const INVALID_FIELD_VALUE = 501;
    const EMAIL_ALREADY_EXISTS = 502;
    const PASSWORD_STRUCTURE_INVALID = 503;
    const INCORRECT_PASSWORD_FOR_EXISTING_EMAIL = 504;
    const MISSING_PASSWORD_FOR_EXISTING_EMAIL = 505;
    const TRIAL_ACCOUNT_ALREADY_EXIST_FOR_EMAIL = 506;

    // Redefine the exception so message/code isn't optional
    public function __construct($message, $code) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code);

    }
}

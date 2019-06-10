<?php
/**
 * partner service allows you to change/manage your partner personal details and settings as well
 *
 * @service partner
 * @package api
 * @subpackage services
 */
class PartnerService extends VidiunBaseService 
{
    
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'register') {
			return false;
		}
		return parent::partnerRequired($actionName);
	}

	/**
	 * Create a new Partner object
	 * 
	 * @action register
	 * @param VidiunPartner $partner
	 * @param string $cmsPassword
	 * @param int $templatePartnerId
	 * @param bool $silent
	 * @return VidiunPartner
	 * @vsOptional
	 *
	 * @throws APIErrors::PARTNER_REGISTRATION_ERROR
	 */
	public function registerAction( VidiunPartner $partner , $cmsPassword = "" , $templatePartnerId = null, $silent = false)
	{
		VidiunResponseCacher::disableCache();
		
		$dbPartner = $partner->toPartner();
		
		$c = new Criteria();
		$c->addAnd(UserLoginDataPeer::LOGIN_EMAIL, $partner->adminEmail, Criteria::EQUAL);
		$existingUser = UserLoginDataPeer::doSelectOne($c);
		/*@var $exisitingUser UserLoginData */

		try
		{
			if ( $cmsPassword == "" ) {
				$cmsPassword = null;
			}
			
			
			$parentPartnerId = null;
			$isAdminOrVarConsole = false;
			if ( $this->getVs() && $this->getVs()->isAdmin() )
			{
				$parentPartnerId = $this->getVs()->partner_id;
				if ($parentPartnerId == Partner::ADMIN_CONSOLE_PARTNER_ID) {
		                    $parentPartnerId = null;
		                    $isAdminOrVarConsole = true;
				}
                else
                {
					// only if this partner is a var/group, allow setting it as parent for the new created partner
					$parentPartner = PartnerPeer::retrieveByPK( $parentPartnerId );
					if ( ! ($parentPartner->getPartnerGroupType() == PartnerGroupType::VAR_GROUP ||
							$parentPartner->getPartnerGroupType() == PartnerGroupType::GROUP ) )
					{
						throw new VidiunAPIException( VidiunErrors::NON_GROUP_PARTNER_ATTEMPTING_TO_ASSIGN_CHILD , $parentPartnerId );
					}
					$isAdminOrVarConsole = true;
					if ($templatePartnerId)
					{
					    $templatePartner = PartnerPeer::retrieveByPK($templatePartnerId);
					    if (!$templatePartner || $templatePartner->getPartnerParentId() != $parentPartnerId)
					        throw new VidiunAPIException( VidiunErrors::NON_GROUP_PARTNER_ATTEMPTING_TO_ASSIGN_CHILD , $parentPartnerId );
					}
				}
			}
			
			$partner_registration = new myPartnerRegistration ( $parentPartnerId );
			
			$ignorePassword = false;
			if ($existingUser && $isAdminOrVarConsole){
				vuserPeer::setUseCriteriaFilter(false);
				$vuserOfLoginData = vuserPeer::getVuserByEmail($partner->adminEmail, $existingUser->getConfigPartnerId());
				vuserPeer::setUseCriteriaFilter(true);
				if ($vuserOfLoginData && (!$parentPartnerId || ($parentPartnerId == $existingUser->getConfigPartnerId())))
					$ignorePassword = true;
			}

			list($pid, $subpid, $pass, $hashKey) = $partner_registration->initNewPartner( $dbPartner->getName() , $dbPartner->getAdminName() , $dbPartner->getAdminEmail() ,
				$dbPartner->getCommercialUse() , "yes" , $dbPartner->getDescription() , $dbPartner->getUrl1() , $cmsPassword , $dbPartner, $ignorePassword, $templatePartnerId );

			$dbPartner = PartnerPeer::retrieveByPK( $pid );

			// send a confirmation email as well as the result of the service
			$partner_registration->sendRegistrationInformationForPartner( $dbPartner , false, $existingUser, $silent );

		}
		catch ( SignupException $se )
		{
//			$this->addError( APIErrors::PARTNER_REGISTRATION_ERROR , $se->getMessage() );
//			return;
			throw new VidiunAPIException( APIErrors::PARTNER_REGISTRATION_ERROR, $se->getMessage());
		}
		catch ( Exception $ex )
		{
			VidiunLog::CRIT($ex);
			// this assumes the partner name is unique - TODO - remove key from DB !
			throw new VidiunAPIException( APIErrors::PARTNER_REGISTRATION_ERROR, 'Unknown error');
		}		
		
		$partner = new VidiunPartner(); // start from blank
		$partner->fromPartner( $dbPartner );
		$partner->secret = $dbPartner->getSecret();
		$partner->adminSecret = $dbPartner->getAdminSecret();
		$partner->cmsPassword = $pass;
		
		return $partner;
	}


	/**
	 * Update details and settings of an existing partner
	 * 
	 * @action update
	 * @param VidiunPartner $partner
	 * @param bool $allowEmpty
	 * @return VidiunPartner
	 *
	 * @throws APIErrors::UNKNOWN_PARTNER_ID
	 */	
	public function updateAction( VidiunPartner $partner, $allowEmpty = false)
	{
		$vars_arr=get_object_vars($partner);
		foreach ($vars_arr as $key => $val){
		    if (is_string($partner->$key)){
                        $partner->$key=strip_tags($partner->$key);
                    }    
                }   
		$dbPartner = PartnerPeer::retrieveByPK( $this->getPartnerId() );
		
		if ( ! $dbPartner )
			throw new VidiunAPIException ( APIErrors::UNKNOWN_PARTNER_ID , $this->getPartnerId() );
		
		try {
			$dbPartner = $partner->toUpdatableObject($dbPartner);
			$dbPartner->save();
		}
		catch(vUserException $e) {
			if ($e->getCode() === vUserException::USER_NOT_FOUND) {
				throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
			}
			throw $e;
		}
		catch(vPermissionException $e) {
			if ($e->getCode() === vPermissionException::ACCOUNT_OWNER_NEEDS_PARTNER_ADMIN_ROLE) {
				throw new VidiunAPIException(VidiunErrors::ACCOUNT_OWNER_NEEDS_PARTNER_ADMIN_ROLE);
			}
			throw $e;			
		}		
		
		$partner = new VidiunPartner();
		$partner->fromPartner( $dbPartner );
		
		return $partner;
	}
	
	
	/**
	 * Retrieve partner object by Id
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunPartner
	 *
	 * @throws APIErrors::INVALID_PARTNER_ID
	 */
	public function getAction ($id = null)
	{
	    if (is_null($id))
	    {
	        $id = $this->getPartnerId();
	    }
	    
	    $c = PartnerPeer::getDefaultCriteria();
	    
		$c->addAnd(PartnerPeer::ID ,$id);
		
		$dbPartner = PartnerPeer::doSelectOne($c);
		if (is_null($dbPartner))
		{
		    throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $id);
		}

		if($this->getPartnerId() != $id)
		{
			myPartnerUtils::addPartnerToCriteria('vuser', $id, true);
		}

		$partner = new VidiunPartner();
		$partner->fromObject($dbPartner, $this->getResponseProfile());
		
		return $partner;
	}

	/**
	 * Retrieve partner secret and admin secret
	 * 
	 * @action getSecrets
	 * @param int $partnerId
	 * @param string $adminEmail
	 * @param string $cmsPassword
	 * @return VidiunPartner
	 * @vsIgnored
	 *
	 * @throws APIErrors::ADMIN_VUSER_NOT_FOUND
	 */
	public function getSecretsAction( $partnerId , $adminEmail , $cmsPassword )
	{
		VidiunResponseCacher::disableCache();

		$adminVuser = null;
		try {
			$adminVuser = UserLoginDataPeer::userLoginByEmail($adminEmail, $cmsPassword, $partnerId);
		}
		catch (vUserException $e) {
			throw new VidiunAPIException ( APIErrors::ADMIN_VUSER_NOT_FOUND, "The data you entered is invalid" );
		}
		
		if (!$adminVuser || !$adminVuser->getIsAdmin()) {
			throw new VidiunAPIException ( APIErrors::ADMIN_VUSER_NOT_FOUND, "The data you entered is invalid" );
		}
		
		VidiunLog::log( "Admin Vuser found, going to validate password", VidiunLog::INFO );
		
		// user logged in - need to re-init vPermissionManager in order to determine current user's permissions
		$vs = null;
		vSessionUtils::createVSessionNoValidations ( $partnerId ,  $adminVuser->getPuserId() , $vs , 86400 , $adminVuser->getIsAdmin() , "" , '*' );
		vCurrentContext::initVsPartnerUser($vs);
		vPermissionManager::init();		
		
		$dbPartner = PartnerPeer::retrieveByPK( $partnerId );
		$partner = new VidiunPartner();
		$partner->fromPartner( $dbPartner );
		$partner->cmsPassword = $cmsPassword;
		
		return $partner;
	}
	
	/**
	 * Retrieve all info attributed to the partner
	 * This action expects no parameters. It returns information for the current VS partnerId.
	 * 
	 * @action getInfo
	 * @return VidiunPartner
	 * @deprecated
	 * @throws APIErrors::UNKNOWN_PARTNER_ID
	 */		
	public function getInfoAction( )
	{
		return $this->getAction();
	}
	
	/**
	 * Get usage statistics for a partner
	 * Calculation is done according to partner's package
	 *
	 * Additional data returned is a graph points of streaming usage in a time frame
	 * The resolution can be "days" or "months"
	 *
	 * @link http://docs.vidiun.org/api/partner/usage
	 * @action getUsage
	 * @param int $year
	 * @param int $month
	 * @param VidiunReportInterval $resolution
	 * @return VidiunPartnerUsage
	 * 
	 * @throws APIErrors::UNKNOWN_PARTNER_ID
	 * @deprecated use getStatistics instead
	 */
	public function getUsageAction($year = '', $month = 1, $resolution = "days")
	{
		$dbPartner = PartnerPeer::retrieveByPK($this->getPartnerId());
		
		if(!$dbPartner)
			throw new VidiunAPIException(APIErrors::UNKNOWN_PARTNER_ID, $this->getPartnerId());
		
		$packages = new PartnerPackages();
		$partnerUsage = new VidiunPartnerUsage();
		$partnerPackage = $packages->getPackageDetails($dbPartner->getPartnerPackage());
		
		$report_date = date("Y-m-d", time());
		
		list($totalStorage, $totalUsage, $totalTraffic) = myPartnerUtils::collectPartnerUsageFromDWH($dbPartner, $partnerPackage, $report_date);
		
		$partnerUsage->hostingGB = round($totalStorage / 1024, 2); // from MB to GB
		$totalUsageGB = round($totalUsage / 1024 / 1024, 2); // from KB to GB
		if($partnerPackage)
		{
			$partnerUsage->Percent = round(($totalUsageGB / $partnerPackage['cycle_bw']) * 100, 2);
			$partnerUsage->packageBW = $partnerPackage['cycle_bw'];
		}
		$partnerUsage->usageGB = $totalUsageGB;
		$partnerUsage->reachedLimitDate = $dbPartner->getUsageLimitWarning();
		
		if($year != '')
		{
			$startDate = gmmktime(0, 0, 0, $month, 1, $year);
			$endDate = gmmktime(0, 0, 0, $month, date('t', $startDate), $year);
			
			if($resolution == reportInterval::MONTHS)
			{
				$startDate = gmmktime(0, 0, 0, 1, 1, $year);
				$endDate = gmmktime(0, 0, 0, 12, 31, $year);
				
				if(intval(date('Y')) == $year)
					$endDate = time();
			}
			
			$usageGraph = myPartnerUtils::getPartnerUsageGraph($startDate, $endDate, $dbPartner, $resolution);
			// currently we provide only one line, output as a string.
			// in the future this could be extended to something like VidiunGraphLines object
			$partnerUsage->usageGraph = $usageGraph;
		}
		
		return $partnerUsage;
	}
	
	/**
	 * Get usage statistics for a partner
	 * Calculation is done according to partner's package
	 *
	 * @action getStatistics
	 * @return VidiunPartnerStatistics
	 * 
	 * @throws APIErrors::UNKNOWN_PARTNER_ID
	 */
	public function getStatisticsAction()
	{
		$dbPartner = PartnerPeer::retrieveByPK($this->getPartnerId());
		
		if(!$dbPartner)
			throw new VidiunAPIException(APIErrors::UNKNOWN_PARTNER_ID, $this->getPartnerId());
		
		$packages = new PartnerPackages();
		$partnerUsage = new VidiunPartnerStatistics();
		$partnerPackage = $packages->getPackageDetails($dbPartner->getPartnerPackage());
		
		$report_date = date("Y-m-d", time());
		
		list($totalStorage, $totalUsage, $totalTraffic) = myPartnerUtils::collectPartnerStatisticsFromDWH($dbPartner, $partnerPackage, $report_date);
		
		$partnerUsage->hosting = round($totalStorage / 1024, 2); // from MB to GB
		$totalUsageGB = round($totalUsage / 1024 / 1024, 2); // from KB to GB
		if($partnerPackage)
		{
			$partnerUsage->usagePercent = round(($totalUsageGB / $partnerPackage['cycle_bw']) * 100, 2);
			$partnerUsage->packageBandwidthAndStorage = $partnerPackage['cycle_bw'];
		}
		if($totalTraffic)
		{
			$partnerUsage->bandwidth = round($totalTraffic / 1024 / 1024, 2); // from KB to GB
		}
		$partnerUsage->usage = $totalUsageGB;
		$partnerUsage->reachedLimitDate = $dbPartner->getUsageLimitWarning();
		
		return $partnerUsage;
	}
	
	/**
	 * Retrieve a list of partner objects which the current user is allowed to access.
	 * 
	 * @action listPartnersForUser
	 * @param VidiunPartnerFilter $partnerFilter
	 * @param VidiunFilterPager $pager
	 * @return VidiunPartnerListResponse
	 * @throws VidiunErrors::INVALID_USER_ID
	 * 
	 */
	public function listPartnersForUserAction(VidiunPartnerFilter $partnerFilter = null, VidiunFilterPager $pager = null)
	{
		$partnerId = vCurrentContext::getCurrentPartnerId();
		$c = new Criteria();
		$currentUser = vuserPeer::getVuserByPartnerAndUid($partnerId, vCurrentContext::$vs_uid, true);

		if(!$currentUser)
		{
		    throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
		}
		
		if (!$pager)
		{
		    $pager = new VidiunFilterPager();
		}
		
		$dbFilter = null;
		if ($partnerFilter)
		{
		    $dbFilter = new partnerFilter();
		    $partnerFilter->toObject($dbFilter);
		}	
			
		$allowedIds = $currentUser->getAllowedPartnerIds($dbFilter);
		$pager->attachToCriteria($c);
		$partners = myPartnerUtils::getPartnersArray($allowedIds, $c);	
		$vidiunPartners = VidiunPartnerArray::fromPartnerArray($partners);
		$response = new VidiunPartnerListResponse();
		$response->objects = $vidiunPartners;
		$response->totalCount = count($partners);	
		
		return $response;
	}

	/**
	 * List partners by filter with paging support
	 * Current implementation will only list the sub partners of the partner initiating the API call (using the current VS).
	 * This action is only partially implemented to support listing sub partners of a VAR partner.
	 * @action list
	 * @param VidiunPartnerFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunPartnerListResponse
	 */
	public function listAction(VidiunPartnerFilter $filter = null, VidiunFilterPager $pager = null)
	{
	    if (is_null($filter))
	    {
	        $filter = new VidiunPartnerFilter();
	    }
	    
	    if (is_null($pager))
	    {
	        $pager = new VidiunFilterPager();   
	    }
	    
	    $partnerFilter = new partnerFilter();
	    $filter->toObject($partnerFilter);
	    
	    $c = PartnerPeer::getDefaultCriteria();
		
	    $partnerFilter->attachToCriteria($c);
		$response = new VidiunPartnerListResponse();
		$response->totalCount = PartnerPeer::doCount($c);
		
	    $pager->attachToCriteria($c);
	    $dbPartners = PartnerPeer::doSelect($c);
	    
		$partnersArray = VidiunPartnerArray::fromPartnerArray($dbPartners);
		
		$response->objects = $partnersArray;
		return $response;
	}
	
	/**
	 * List partner's current processes' statuses
	 * 
	 * @action listFeatureStatus
	 * @throws APIErrors::UNKNOWN_PARTNER_ID
	 * @return VidiunFeatureStatusListResponse
	 */
	public function listFeatureStatusAction()
	{
		if (is_null($this->getVs()) || is_null($this->getPartner()) || !$this->getPartnerId())
			throw new VidiunAPIException(APIErrors::MISSING_VS);
			
		$dbPartner = $this->getPartner();
		if ( ! $dbPartner )
			throw new VidiunAPIException ( APIErrors::UNKNOWN_PARTNER_ID , $this->getPartnerId() );
		
		$dbFeaturesStatus = $dbPartner->getFeaturesStatus();
		
		$featuresStatus = VidiunFeatureStatusArray::fromDbArray($dbFeaturesStatus, $this->getResponseProfile());
		
		$response = new VidiunFeatureStatusListResponse();
		$response->objects = $featuresStatus;
		$response->totalCount = count($featuresStatus);
		
		return $response;
	}
	
	/**
	 * Count partner's existing sub-publishers (count includes the partner itself).
	 * 
	 * @action count
	 * @param VidiunPartnerFilter $filter
	 * @return int
	 */
	public function countAction (VidiunPartnerFilter $filter = null)
	{
	    if (!$filter)
		$filter = new VidiunPartnerFilter();
		
	    $dbFilter = new partnerFilter();
	    $filter->toObject($dbFilter);
	    
	    $c = PartnerPeer::getDefaultCriteria();
	    $dbFilter->attachToCriteria($c);
	    
	    return PartnerPeer::doCount($c);
	}

	/**
	 * Returns partner public info by Id
	 *
	 * @action getPublicInfo
	 * @param int $id
	 * @return VidiunPartnerPublicInfo
	 *
	 * @throws APIErrors::INVALID_PARTNER_ID
	 */
	public function getPublicInfoAction ($id = null)
	{
		if (!$id)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $id);
		}

		$dbPartner = PartnerPeer::retrieveByPK($id);
		if (is_null($dbPartner))
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $id);
		}

		$response = new VidiunPartnerPublicInfo();
		$response->fromObject($dbPartner, $this->getResponseProfile());

		return $response;
	}
	
}

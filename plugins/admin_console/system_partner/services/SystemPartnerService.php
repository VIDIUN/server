<?php
/**
 * System partner service
 *
 * @service systemPartner
 * @package plugins.systemPartner
 * @subpackage api.services
 */
class SystemPartnerService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		// since plugin might be using VS impersonation, we need to validate the requesting
		// partnerId from the VS and not with the $_POST one
		if(!SystemPartnerPlugin::isAllowedPartner(vCurrentContext::$master_partner_id))
			throw new VidiunAPIException(SystemPartnerErrors::FEATURE_FORBIDDEN, SystemPartnerPlugin::PLUGIN_NAME);
	}

	
	/**
	 * Retrieve all info about partner
	 * This service gets partner id as parameter and accessable to the admin console partner only
	 * 
	 * @action get
	 * @param int $pId
	 * @return VidiunPartner
	 *
	 * @throws APIErrors::UNKNOWN_PARTNER_ID
	 */		
	function getAction($pId)
	{		
		$dbPartner = PartnerPeer::retrieveByPK( $pId );
		
		if ( ! $dbPartner )
			throw new VidiunAPIException ( APIErrors::UNKNOWN_PARTNER_ID , $pId );
			
		$partner = new VidiunPartner();
		$partner->fromPartner( $dbPartner );
		
		return $partner;
	}
	
	/**
	 * @action getUsage
	 * @param VidiunSystemPartnerUsageFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunSystemPartnerUsageListResponse
	 */
	public function getUsageAction(VidiunPartnerFilter $partnerFilter = null, VidiunSystemPartnerUsageFilter $usageFilter = null, VidiunFilterPager $pager = null)
	{
		if (is_null($partnerFilter))
			$partnerFilter = new VidiunPartnerFilter();
		
		if (is_null($usageFilter))
		{
			$usageFilter = new VidiunSystemPartnerUsageFilter();
			$usageFilter->fromDate = time() - 60*60*24*30; // last 30 days
			$usageFilter->toDate = time();
			$usageFilter->timezoneOffset = 0;
		}
		
		if (is_null($pager))
			$pager = new VidiunFilterPager();

		$partnerFilterDb = new partnerFilter();
		$partnerFilter->toObject($partnerFilterDb);
		$partnerFilterDb->set('_gt_id', 0);
		
		// total count
		$c = new Criteria();
		$partnerFilterDb->attachToCriteria($c);
		$totalCount = PartnerPeer::doCount($c);
		
		// filter partners criteria
		$pager->attachToCriteria($c);
		$c->addAscendingOrderByColumn(PartnerPeer::ID);
		
		// select partners
		$partners = PartnerPeer::doSelect($c);
		$partnerIds = array();
		foreach($partners as &$partner)
			$partnerIds[] = $partner->getId();
		
		$items = array();
		if ( ! count($partnerIds ) )
		{
			// no partners fit the filter - don't fetch data	
			$totalCount = 0;
			// the items are set to an empty VidiunSystemPartnerUsageArray
		}
		else
		{
			$inputFilter = new reportsInputFilter (); 
			$inputFilter->from_date = ( $usageFilter->fromDate );
			$inputFilter->to_date = ( $usageFilter->toDate );
			$inputFilter->from_day = date ( "Ymd" , $usageFilter->fromDate );
			$inputFilter->to_day = date ( "Ymd" , $usageFilter->toDate );
		
			$inputFilter->timeZoneOffset = $usageFilter->timezoneOffset;
	
			list ( $reportHeader, $reportData, $totalCountNoNeeded) = vKavaReportsMgr::getTable(
			    null ,
			    myReportsMgr::REPORT_TYPE_ADMIN_CONSOLE ,
			    $inputFilter ,
			    $pager->pageSize , 0 ,
			    null ,  
			    implode("," , $partnerIds ) );
			
			
			$unsortedItems = array();
			foreach ( $reportData as $line )
			{
				$item = VidiunSystemPartnerUsageItem::fromString( $reportHeader , $line );
				if ( $item )	
					$unsortedItems[$item->partnerId] = $item;	
			}
					
			// create the items in the order of the partnerIds and create some dummy for ones that don't exist
			foreach ( $partnerIds as $partnerId )
			{
				if ( isset ( $unsortedItems[$partnerId] ))
					$items[] = $unsortedItems[$partnerId];
				else
				{
					// if no item for partner - get its details from the db
					$items[] = VidiunSystemPartnerUsageItem::fromPartner(PartnerPeer::retrieveByPK($partnerId));
				}  
			}
		}
		$response = new VidiunSystemPartnerUsageListResponse();
		$response->totalCount = $totalCount;
		$response->objects = $items;
		return $response;
	}
		

	
	/**
	 * @action list
	 * @param VidiunPartnerFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunPartnerListResponse
	 */
	public function listAction(VidiunPartnerFilter $filter = null, VidiunFilterPager $pager = null)
	{
	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;
		
		if (is_null($filter))
			$filter = new VidiunPartnerFilter();
			
		if (is_null($pager))
			$pager = new VidiunFilterPager();

		$partnerFilter = new partnerFilter();
		$filter->toObject($partnerFilter);
		$partnerFilter->set('_gt_id', 0);
		
		$c = new Criteria();
		$partnerFilter->attachToCriteria($c);
		
		$totalCount = PartnerPeer::doCount($c);
		$pager->attachToCriteria($c);
		$list = PartnerPeer::doSelect($c);
		$newList = VidiunPartnerArray::fromPartnerArray($list);
		
		$response = new VidiunPartnerListResponse();
		$response->totalCount = $totalCount;
		$response->objects = $newList;
		return $response;
	}
	
	/**
	 * @action updateStatus
	 * @param int $id
	 * @param VidiunPartnerStatus $status
	 * @param string $reason
	 */
	public function updateStatusAction($id, $status, $reason)
	{
		$dbPartner = PartnerPeer::retrieveByPK($id);
		if (!$dbPartner)
			throw new VidiunAPIException(VidiunErrors::UNKNOWN_PARTNER_ID, $id);
			
		$dbPartner->setStatus($status);
		$dbPartner->setStatusChangeReason( $reason );
		$dbPartner->save();
		PartnerPeer::removePartnerFromCache($id);
	}
	
	/**
	 * @action getAdminSession
	 * @param int $pId
	 * @param string $userId
	 * @return string
	 */
	public function getAdminSessionAction($pId, $userId = null)
	{
		$dbPartner = PartnerPeer::retrieveByPK($pId);
		if (!$dbPartner)
			throw new VidiunAPIException(VidiunErrors::UNKNOWN_PARTNER_ID, $pId);
		
		if (!$userId) {
			$userId = $dbPartner->getAdminUserId();
		}
		
		$vuser = vuserPeer::getVuserByPartnerAndUid($pId, $userId);
		if (!$vuser) {
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
		}
		if (!$vuser->getIsAdmin()) {
			throw new VidiunAPIException(VidiunErrors::USER_NOT_ADMIN, $userId);
		}
			
		$vs = "";
		vSessionUtils::createVSessionNoValidations($dbPartner->getId(), $userId, $vs, 86400, 2, "", '*,' . vs::PRIVILEGE_DISABLE_ENTITLEMENT);
		return $vs;
	}
	
	/**
	 * @action updateConfiguration
	 * @param int $pId
	 * @param VidiunSystemPartnerConfiguration $configuration
	 */
	public function updateConfigurationAction($pId, VidiunSystemPartnerConfiguration $configuration)
	{
		$dbPartner = PartnerPeer::retrieveByPK($pId);
		if (!$dbPartner)
			throw new VidiunAPIException(VidiunErrors::UNKNOWN_PARTNER_ID, $pId);
		$configuration->toUpdatableObject($dbPartner);
		$dbPartner->save();
		PartnerPeer::removePartnerFromCache($pId);
	}
	
	/**
	 * @action getConfiguration
	 * @param int $pId
	 * @return VidiunSystemPartnerConfiguration
	 */
	public function getConfigurationAction($pId)
	{
		$dbPartner = PartnerPeer::retrieveByPK($pId);
		if (!$dbPartner)
			throw new VidiunAPIException(VidiunErrors::UNKNOWN_PARTNER_ID, $pId);
			
		$configuration = new VidiunSystemPartnerConfiguration();
		$configuration->fromObject($dbPartner, $this->getResponseProfile());
		return $configuration;
	}
	
	/**
	 * @action getPackages
	 * @return VidiunSystemPartnerPackageArray
	 */
	public function getPackagesAction()
	{
		$partnerPackages = new PartnerPackages();
		$packages = $partnerPackages->listPackages();
		$partnerPackages = new VidiunSystemPartnerPackageArray();
		$partnerPackages->fromArray($packages);
		return $partnerPackages;
	}
	
	/**
	 * @action getPackagesClassOfService
	 * @return VidiunSystemPartnerPackageArray
	 */
	public function getPackagesClassOfServiceAction()
	{
		$partnerPackages = new PartnerPackages();
		$packages = $partnerPackages->listPackagesClassOfService();
		$partnerPackages = new VidiunSystemPartnerPackageArray();
		$partnerPackages->fromArray($packages);
		return $partnerPackages;
	}
	
	/**
	 * @action getPackagesVertical
	 * @return VidiunSystemPartnerPackageArray
	 */
	public function getPackagesVerticalAction()
	{
		$partnerPackages = new PartnerPackages();
		$packages = $partnerPackages->listPackagesVertical();
		$partnerPackages = new VidiunSystemPartnerPackageArray();
		$partnerPackages->fromArray($packages);
		return $partnerPackages;
	}
	
	/**
	 * @action getPlayerEmbedCodeTypes
	 * @return VidiunPlayerEmbedCodeTypesArray
	 */
	public function getPlayerEmbedCodeTypesAction()
	{
		$map = vConf::getMap('players');
		return VidiunPlayerEmbedCodeTypesArray::fromDbArray($map['embed_code_types'], $this->getResponseProfile());
	}
	
	/**
	 * @action getPlayerDeliveryTypes
	 * @return VidiunPlayerDeliveryTypesArray
	 */
	public function getPlayerDeliveryTypesAction()
	{
		$map = vConf::getMap('players');
		return VidiunPlayerDeliveryTypesArray::fromDbArray($map['delivery_types'], $this->getResponseProfile());
	}

	/**
	 * 
	 * @action resetUserPassword
	 * @param string $userId
	 * @param int $pId
	 * @param string $newPassword
	 * @throws VidiunAPIException
	 */
	public function resetUserPasswordAction($userId, $pId, $newPassword)
	{
		if ($pId == Partner::ADMIN_CONSOLE_PARTNER_ID || $pId == Partner::BATCH_PARTNER_ID)
		{
			throw new VidiunAPIException(VidiunErrors::CANNOT_RESET_PASSWORD_FOR_SYSTEM_PARTNER);
		}				
		//get loginData using userId and PartnerId 
		$vuser = vuserPeer::getVuserByPartnerAndUid ($pId, $userId);
		if (!$vuser){
			throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
		}
		$userLoginDataId = $vuser->getLoginDataId();
		$userLoginData = UserLoginDataPeer::retrieveByPK($userLoginDataId);
		
		// check if login data exists
		if (!$userLoginData) {
			throw new VidiunAPIException(VidiunErrors::LOGIN_DATA_NOT_FOUND);
		}
		try {
			UserLoginDataPeer::checkPasswordValidation($newPassword, $userLoginData);
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::PASSWORD_STRUCTURE_INVALID) {
				$passwordRules = $userLoginData->getInvalidPasswordStructureMessage();
				$passwordRules = str_replace( "\\n", "<br>", $passwordRules );
				$passwordRules = "<br>" . $passwordRules; // Add a newline prefix
				throw new VidiunAPIException(VidiunErrors::PASSWORD_STRUCTURE_INVALID, $passwordRules);
			}
			else if ($code == vUserException::PASSWORD_ALREADY_USED) {
				throw new VidiunAPIException(VidiunErrors::PASSWORD_ALREADY_USED);
			}			
			throw new VidiunAPIException(VidiunErrors::INTERNAL_SERVERL_ERROR);						
		}
		// update password if requested
		if ($newPassword) {
			$password = $userLoginData->resetPassword($newPassword);
		}		
		$userLoginData->save();
	}
	
	
	/**
	 * @action listUserLoginData
	 * @param VidiunUserLoginDataFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunUserLoginDataListResponse
	 */
	public function listUserLoginDataAction(VidiunUserLoginDataFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (is_null($filter))
			$filter = new VidiunUserLoginDataFilter();
			
		if (is_null($pager))
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	
}

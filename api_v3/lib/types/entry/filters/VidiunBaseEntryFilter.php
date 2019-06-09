<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunBaseEntryFilter extends VidiunBaseEntryBaseFilter
{
	static private $map_between_objects = array
	(
		"freeText" => "_free_text",
		"isRoot" => "_is_root",
		"categoriesFullNameIn" => "_in_categories_full_name", 
		"categoryAncestorIdIn" => "_in_category_ancestor_id",
		"redirectFromEntryId" => "_eq_redirect_from_entry_id",
		"entitledUsersEditMatchAnd" => "_matchand_entitled_vusers_edit",
		"entitledUsersPublishMatchAnd" => "_matchand_entitled_vusers_publish",
		"entitledUsersEditMatchOr" => "_matchor_entitled_vusers_edit",
		"entitledUsersPublishMatchOr" => "_matchor_entitled_vusers_publish",
		"entitledUsersViewMatchAnd" => "_matchand_entitled_vusers_view",
		"entitledUsersViewMatchOr" => "_matchor_entitled_vusers_view",
	);
	
	static private $order_by_map = array
	(
		"recent" => "recent", // needed for backward compatibility
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), self::$order_by_map);
	}

	/**
	 * @var string
	 */
	public $freeText;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $isRoot;
	
	/**
	 * @var string
	 */
	public $categoriesFullNameIn;
	
	/**
	 * All entries within this categoy or in child categories  
	 * @var string
	 */
	public $categoryAncestorIdIn;

	/**
	 * The id of the original entry
	 * @var string
	 */
	public $redirectFromEntryId;

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new entryFilter();
	}
	
	/**
	 * Set the default status to ready if other status filters are not specified
	 */
	private function setDefaultStatus()
	{
		if ($this->statusEqual === null && 
			$this->statusIn === null &&
			$this->statusNotEqual === null &&
			$this->statusNotIn === null)
		{
			$this->statusEqual = VidiunEntryStatus::READY;
		}
	}
	
	/**
	 * Set the default moderation status to ready if other moderation status filters are not specified
	 */
	private function setDefaultModerationStatus()
	{
		if ($this->moderationStatusEqual === null && 
			$this->moderationStatusIn === null && 
			$this->moderationStatusNotEqual === null && 
			$this->moderationStatusNotIn === null)
		{
			$moderationStatusesNotIn = array(
				VidiunEntryModerationStatus::PENDING_MODERATION, 
				VidiunEntryModerationStatus::REJECTED);
			$this->moderationStatusNotIn = implode(",", $moderationStatusesNotIn); 
		}
	}

	/**
	 * The user_id is infact a puser_id and the vuser_id should be retrieved
	 */
	private function fixFilterUserId()
	{
		if ($this->userIdEqual !== null)
		{
			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::getCurrentPartnerId(), $this->userIdEqual);
			if ($vuser)
				$this->userIdEqual = $vuser->getId();
			else 
				$this->userIdEqual = -1; // no result will be returned when the user is missing
		}

		if(!empty($this->userIdIn))
		{
			$this->userIdIn = $this->preparePusersToVusersFilter( $this->userIdIn );
		}

		if(!empty($this->userIdNotIn))
		{
			$this->userIdNotIn = $this->preparePusersToVusersFilter($this->userIdNotIn);
		}
		
		if(!empty($this->entitledUsersEditMatchAnd))
		{
			$this->entitledUsersEditMatchAnd = $this->preparePusersToVusersFilter( $this->entitledUsersEditMatchAnd );
		}

		if(!empty($this->entitledUsersPublishMatchAnd))
		{
			$this->entitledUsersPublishMatchAnd = $this->preparePusersToVusersFilter( $this->entitledUsersPublishMatchAnd );
		}
		
		if(!empty($this->entitledUsersEditMatchOr))
		{
			$this->entitledUsersEditMatchOr = $this->preparePusersToVusersFilter( $this->entitledUsersEditMatchOr );
		}

		if(!empty($this->entitledUsersPublishMatchOr))
		{
			$this->entitledUsersPublishMatchOr = $this->preparePusersToVusersFilter( $this->entitledUsersPublishMatchOr );
		}
		
		if(!empty($this->entitledUsersViewMatchOr))
		{
			$this->entitledUsersViewMatchOr = $this->preparePusersToVusersFilter( $this->entitledUsersViewMatchOr );
		}

		if(!empty($this->entitledUsersViewMatchAnd))
		{
			$this->entitledUsersViewMatchAnd = $this->preparePusersToVusersFilter( $this->entitledUsersViewMatchAnd );
		}
	}
	
	/**
	 * @param VidiunFilterPager $pager
	 * @return VidiunCriteria
	 */
	public function prepareEntriesCriteriaFilter(VidiunFilterPager $pager = null)
	{
		// because by default we will display only READY entries, and when deleted status is requested, we don't want this to disturb
		entryPeer::allowDeletedInCriteriaFilter(); 
		
		$c = VidiunCriteria::create(entryPeer::OM_CLASS);
	
		if( $this->idEqual == null && $this->redirectFromEntryId == null )
		{
			$this->setDefaultStatus();
			$this->setDefaultModerationStatus($this);
			if(($this->parentEntryIdEqual == null) && ($this->idIn == null))
				$c->add(entryPeer::DISPLAY_IN_SEARCH, mySearchUtils::DISPLAY_IN_SEARCH_SYSTEM, Criteria::NOT_EQUAL);
		}
		
		$this->fixFilterUserId($this);
		
		$entryFilter = new entryFilter();
		$entryFilter->setPartnerSearchScope(baseObjectFilter::MATCH_VIDIUN_NETWORK_AND_PRIVATE);
		
		$this->toObject($entryFilter);
		
		if($pager)
			$pager->attachToCriteria($c);
			
		$entryFilter->attachToCriteria($c);
		
		return $c;
	}
	
	protected function doGetListResponse(VidiunFilterPager $pager)
	{
		myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;

		$disableWidgetSessionFilters = false;
		if ($this &&
			($this->idEqual != null ||
			$this->idIn != null ||
			$this->referenceIdEqual != null ||
			$this->redirectFromEntryId != null ||
			$this->referenceIdIn != null || 
			$this->parentEntryIdEqual != null))
			$disableWidgetSessionFilters = true;
			
		$c = $this->prepareEntriesCriteriaFilter($pager);
		
		if ($disableWidgetSessionFilters)
		{
			if (vEntitlementUtils::getEntitlementEnforcement() && !vCurrentContext::$is_admin_session && entryPeer::getUserContentOnly())
					entryPeer::setFilterResults(true);

			VidiunCriterion::disableTag(VidiunCriterion::TAG_WIDGET_SESSION);
		}
		$list = entryPeer::doSelect($c);
		entryPeer::fetchPlaysViewsData($list);
		$totalCount = $c->getRecordsCount();
		
		if ($disableWidgetSessionFilters)
			VidiunCriterion::restoreTag(VidiunCriterion::TAG_WIDGET_SESSION);

		myDbHelper::$use_alternative_con = null;
			
		return array($list, $totalCount);		
	}
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager);
		
	    $newList = VidiunBaseEntryArray::fromDbArray($list, $responseProfile);
		$response = new VidiunBaseEntryListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::validateForResponseProfile()
	 */
	public function validateForResponseProfile()
	{		
		if(vEntitlementUtils::getEntitlementEnforcement())
		{
			if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENABLE_RESPONSE_PROFILE_USER_CACHE, vCurrentContext::getCurrentPartnerId()))
			{
				VidiunResponseProfileCacher::useUserCache();
				return;
			}
			
			throw new VidiunAPIException(VidiunErrors::CANNOT_LIST_RELATED_ENTITLED_WHEN_ENTITLEMENT_IS_ENABLE, get_class($this));
		}
		
		if(		!vCurrentContext::$is_admin_session
			&&	!$this->idEqual 
			&&	!$this->idIn
			&&	!$this->referenceIdEqual
			&&	!$this->redirectFromEntryId
			&&	!$this->referenceIdIn 
			&&	!$this->parentEntryIdEqual)
		{
			if(vCurrentContext::$vs_object->privileges === vs::PATTERN_WILDCARD || vCurrentContext::$vs_object->getPrivilegeValue(vs::PRIVILEGE_LIST) === vs::PATTERN_WILDCARD)
			{
				return;
			}
			
			if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENABLE_RESPONSE_PROFILE_USER_CACHE, vCurrentContext::getCurrentPartnerId()))
			{
				VidiunResponseProfileCacher::useUserCache();
				return;
			}
			
			throw new VidiunAPIException(VidiunErrors::USER_VS_CANNOT_LIST_RELATED_ENTRIES, get_class($this));
		}
	}
}

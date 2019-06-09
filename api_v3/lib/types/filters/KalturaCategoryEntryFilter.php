<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunCategoryEntryFilter extends VidiunCategoryEntryBaseFilter
{
	
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new categoryEntryFilter();
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$blockOnEmptyFilterPartners = vConf::getMap(vConfMapNames::REQUIRE_CATEGORY_ENTRY_FILTER_PARTNERS);
		if ($this->entryIdEqual == null &&
			$this->entryIdIn == null &&
			$this->categoryIdIn == null &&
			$this->categoryIdEqual == null && 
			(vEntitlementUtils::getEntitlementEnforcement() || !vCurrentContext::$is_admin_session || in_array(vCurrentContext::getCurrentPartnerId(), $blockOnEmptyFilterPartners))
		)
		{
			throw new VidiunAPIException(VidiunErrors::MUST_FILTER_ON_ENTRY_OR_CATEGORY);
		}
			
		if(vEntitlementUtils::getEntitlementEnforcement())
		{
			//validate entitl for entry
			if($this->entryIdEqual != null)
			{
				$entry = entryPeer::retrieveByPK($this->entryIdEqual);
				if(!$entry)
					throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryIdEqual);
			}
			
			//validate entitl for entryIn
			if($this->entryIdIn != null)
			{
				$entry = entryPeer::retrieveByPKs($this->entryIdIn);
				if(!$entry)
					throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryIdIn);
			}
			
			//validate entitl categories
			if($this->categoryIdIn != null)
			{
				$categoryIdInArr = explode(',', $this->categoryIdIn);
				if(!categoryVuserPeer::areCategoriesAllowed($categoryIdInArr))
				$categoryIdInArr = array_unique($categoryIdInArr);
				
				$entitledCategories = categoryPeer::retrieveByPKs($categoryIdInArr);
				
				if(!count($entitledCategories) || count($entitledCategories) != count($categoryIdInArr))
					throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $this->categoryIdIn);
					
				$categoriesIdsUnlisted = array();
				foreach($entitledCategories as $category)
				{
					if($category->getDisplayInSearch() == DisplayInSearchType::CATEGORY_MEMBERS_ONLY)
						$categoriesIdsUnlisted[] = $category->getId();
				}

				if(count($categoriesIdsUnlisted))
				{
					if(!categoryVuserPeer::areCategoriesAllowed($categoriesIdsUnlisted))
						throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $this->categoryIdIn);
				}
			}
			
			//validate entitl category
			if($this->categoryIdEqual != null)
			{
				$category = categoryPeer::retrieveByPK($this->categoryIdEqual);
				if(!$category && vCurrentContext::$master_partner_id != Partner::BATCH_PARTNER_ID)
					throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $this->categoryIdEqual);

				if(($category->getDisplayInSearch() == DisplayInSearchType::CATEGORY_MEMBERS_ONLY) && 
					!categoryVuserPeer::retrievePermittedVuserInCategory($category->getId(), vCurrentContext::getCurrentVsVuserId()))
				{
					throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $this->categoryIdEqual);
				}
			}
		}
			
		$this->fixUserIds();
		$categoryEntryFilter = $this->toObject();
		 
		$c = VidiunCriteria::create(categoryEntryPeer::OM_CLASS);
		$categoryEntryFilter->attachToCriteria($c);
		
		if(!vEntitlementUtils::getEntitlementEnforcement() || $this->entryIdEqual == null)
			$pager->attachToCriteria($c);
			
		$dbCategoriesEntry = categoryEntryPeer::doSelect($c);
		
		if(vEntitlementUtils::getEntitlementEnforcement() && count($dbCategoriesEntry) && $this->entryIdEqual != null)
		{
			//remove unlisted categories: display in search is set to members only
			$categoriesIds = array();
			foreach ($dbCategoriesEntry as $dbCategoryEntry)
				$categoriesIds[] = $dbCategoryEntry->getCategoryId();
				
			$c = VidiunCriteria::create(categoryPeer::OM_CLASS);
			$c->add(categoryPeer::ID, $categoriesIds, Criteria::IN);
			$pager->attachToCriteria($c);
			$c->applyFilters();
			
			$categoryIds = $c->getFetchedIds();
			
			foreach ($dbCategoriesEntry as $key => $dbCategoryEntry)
			{
				if(!in_array($dbCategoryEntry->getCategoryId(), $categoryIds))
				{
					VidiunLog::info('Category [' . print_r($dbCategoryEntry->getCategoryId(),true) . '] is not listed to user');
					unset($dbCategoriesEntry[$key]);
				}
			}
			
			$totalCount = $c->getRecordsCount();
		}
		else
		{
			$resultCount = count($dbCategoriesEntry);
			if ($resultCount && $resultCount < $pager->pageSize)
				$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
			else
			{
				VidiunFilterPager::detachFromCriteria($c);
				$totalCount = categoryEntryPeer::doCount($c);
			}
		}
			
		$categoryEntrylist = VidiunCategoryEntryArray::fromDbArray($dbCategoriesEntry, $responseProfile);
		$response = new VidiunCategoryEntryListResponse();
		$response->objects = $categoryEntrylist;
		$response->totalCount = $totalCount; // no pager since category entry is limited to ENTRY::MAX_CATEGORIES_PER_ENTRY
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
	}
	
	private function fixUserIds ()
	{
		if ($this->creatorUserIdEqual !== null)
		{
			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::getCurrentPartnerId(), $this->creatorUserIdEqual);
			if ($vuser)
				$this->creatorUserIdEqual = $vuser->getId();
			else 
				$this->creatorUserIdEqual = -1; // no result will be returned when the user is missing
		}

		if(!empty($this->creatorUserIdIn))
		{
			$this->creatorUserIdIn = $this->preparePusersToVusersFilter( $this->creatorUserIdIn );
		}
	}
}

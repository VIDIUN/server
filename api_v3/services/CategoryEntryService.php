<?php

/**
 * Add & Manage CategoryEntry - assign entry to category
 *
 * @service categoryEntry
 */
class CategoryEntryService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('category');
		$this->applyPartnerFilterForClass('entry');	
	}
	
	/**
	 * Add new CategoryEntry
	 * 
	 * @action add
	 * @param VidiunCategoryEntry $categoryEntry
	 * @throws VidiunErrors::INVALID_ENTRY_ID
	 * @throws VidiunErrors::CATEGORY_NOT_FOUND
	 * @throws VidiunErrors::CANNOT_ASSIGN_ENTRY_TO_CATEGORY
	 * @throws VidiunErrors::CATEGORY_ENTRY_ALREADY_EXISTS
	 * @return VidiunCategoryEntry
	 */
	function addAction(VidiunCategoryEntry $categoryEntry)
	{
		$categoryEntry->validateForInsert();
		
		$entry = entryPeer::retrieveByPK($categoryEntry->entryId);
		if (!$entry)
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID, $categoryEntry->entryId);
			
		$category = categoryPeer::retrieveByPK($categoryEntry->categoryId);
		if (!$category)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $categoryEntry->categoryId);
			
		$categoryEntries = categoryEntryPeer::retrieveActiveAndPendingByEntryId($categoryEntry->entryId);
		
		$maxCategoriesPerEntry = $entry->getMaxCategoriesPerEntry();
			
		if (count($categoryEntries) >= $maxCategoriesPerEntry)
			throw new VidiunAPIException(VidiunErrors::MAX_CATEGORIES_FOR_ENTRY_REACHED, $maxCategoriesPerEntry);
			
		//validate user is entiteld to assign entry to this category 
		if (vEntitlementUtils::getEntitlementEnforcement() && $category->getContributionPolicy() != ContributionPolicyType::ALL)
		{
			$categoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($categoryEntry->categoryId, vCurrentContext::getCurrentVsVuserId());
			if(!$categoryVuser)
			{
				VidiunLog::err("User [" . vCurrentContext::getCurrentVsVuserId() . "] is not a member of the category [{$categoryEntry->categoryId}]");
				throw new VidiunAPIException(VidiunErrors::CANNOT_ASSIGN_ENTRY_TO_CATEGORY);
			}
			if($categoryVuser->getPermissionLevel() == CategoryVuserPermissionLevel::MEMBER)
			{
				VidiunLog::err("User [" . vCurrentContext::getCurrentVsVuserId() . "] permission level [" . $categoryVuser->getPermissionLevel() . "] on category [{$categoryEntry->categoryId}] is not member [" . CategoryVuserPermissionLevel::MEMBER . "]");
				throw new VidiunAPIException(VidiunErrors::CANNOT_ASSIGN_ENTRY_TO_CATEGORY);
			}
				
			if(!$categoryVuser->hasPermission(PermissionName::CATEGORY_EDIT) && !$categoryVuser->hasPermission(PermissionName::CATEGORY_CONTRIBUTE) &&
				!$entry->isEntitledVuserEdit(vCurrentContext::getCurrentVsVuserId()) &&
				$entry->getCreatorVuserId() != vCurrentContext::getCurrentVsVuserId())
				throw new VidiunAPIException(VidiunErrors::CANNOT_ASSIGN_ENTRY_TO_CATEGORY);				
		}
		
		$categoryEntryExists = categoryEntryPeer::retrieveByCategoryIdAndEntryId($categoryEntry->categoryId, $categoryEntry->entryId);
		if($categoryEntryExists && $categoryEntryExists->getStatus() == CategoryEntryStatus::ACTIVE)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_ENTRY_ALREADY_EXISTS);
		
		if(!$categoryEntryExists)
		{
			$dbCategoryEntry = new categoryEntry();
		}
		else
		{
			$dbCategoryEntry = $categoryEntryExists;
		}
		
		$categoryEntry->toInsertableObject($dbCategoryEntry);
		
		/* @var $dbCategoryEnry categoryEntry */
		$dbCategoryEntry->setStatus(CategoryEntryStatus::ACTIVE);
		
		if (vEntitlementUtils::getEntitlementEnforcement() && $category->getModeration())
		{
			$categoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($categoryEntry->categoryId, vCurrentContext::getCurrentVsVuserId());
			if(!$categoryVuser ||
				($categoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MANAGER && 
				$categoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MODERATOR))
				$dbCategoryEntry->setStatus(CategoryEntryStatus::PENDING);
		}
		
		if ($category->getModeration() && 
		   (vEntitlementUtils::getCategoryModeration() || $this->getPartner()->getEnabledService(VidiunPermissionName::FEATURE_BLOCK_CATEGORY_MODERATION_SELF_APPROVE)))
		{
			$dbCategoryEntry->setStatus(CategoryEntryStatus::PENDING);
		}
		
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		$dbCategoryEntry->setPartnerId($partnerId);
		
		$vuser = vCurrentContext::getCurrentVsVuser();
		
		if ($vuser)
		{
			$dbCategoryEntry->setCreatorVuserId($vuser->getId());
			$dbCategoryEntry->setCreatorPuserId($vuser->getPuserId());
		}
		
		$dbCategoryEntry->save();
		
		//need to select the entry again - after update
		$entry = entryPeer::retrieveByPK($categoryEntry->entryId);		
		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE, $entry);
		
		$categoryEntry = new VidiunCategoryEntry();
		$categoryEntry->fromObject($dbCategoryEntry, $this->getResponseProfile());

		return $categoryEntry;
	}
	
	/**
	 * Delete CategoryEntry
	 * 
	 * @action delete
	 * @param string $entryId
	 * @param int $categoryId
	 * @throws VidiunErrors::INVALID_ENTRY_ID
	 * @throws VidiunErrors::CATEGORY_NOT_FOUND
	 * @throws VidiunErrors::CANNOT_REMOVE_ENTRY_FROM_CATEGORY
	 * @throws VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY
	 * 
	 */
	function deleteAction($entryId, $categoryId)
	{
		$entry = entryPeer::retrieveByPK($entryId);

		if (!$entry)
		{
			if (vCurrentContext::$master_partner_id != Partner::BATCH_PARTNER_ID)
				throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID, $entryId);
		}
			
		$category = categoryPeer::retrieveByPK($categoryId);
		if (!$category && vCurrentContext::$master_partner_id != Partner::BATCH_PARTNER_ID)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $categoryId);

		//validate user is entitled to remove entry from category
		if(vEntitlementUtils::getEntitlementEnforcement() &&
			!$entry->isEntitledVuserEdit(vCurrentContext::getCurrentVsVuserId()) &&
			$entry->getCreatorVuserId() != vCurrentContext::getCurrentVsVuserId())
		{
			$vuserIsEntitled = false;
			$vuser = categoryVuserPeer::retrievePermittedVuserInCategory($categoryId, vCurrentContext::getCurrentVsVuserId());

			// First pass: check if vuser is a manager
			if ( $vuser )
			{
				if ( $vuser->getPermissionLevel() == CategoryVuserPermissionLevel::MANAGER )
				{
					$vuserIsEntitled = true;
				}
			}
			else
			{
				$vuser = vuserPeer::retrieveByPK( vCurrentContext::getCurrentVsVuserId() );
			}

			// Second pass: check if vuser is a co-publisher
			if ( ! $vuserIsEntitled
					&& $vuser
					&& $entry->isEntitledVuserPublish($vuser->getVuserId()))
			{
				$vuserIsEntitled = true;
			}

			if ( ! $vuserIsEntitled )
			{
				throw new VidiunAPIException(VidiunErrors::CANNOT_REMOVE_ENTRY_FROM_CATEGORY);
			}
		}
			
		$dbCategoryEntry = categoryEntryPeer::retrieveByCategoryIdAndEntryId($categoryId, $entryId);
		if(!$dbCategoryEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY);
		
		$dbCategoryEntry->setStatus(CategoryEntryStatus::DELETED);
		$dbCategoryEntry->save();
		
		//need to select the entry again - after update
		$entry = entryPeer::retrieveByPK($entryId);		
		myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE, $entry);
	}
	
	/**
	 * List all categoryEntry
	 * 
	 * @action list
	 * @param VidiunCategoryEntryFilter $filter
	 * @param VidiunFilterPager $pager
	 * @throws VidiunErrors::MUST_FILTER_ENTRY_ID_EQUAL
	 * @throws VidiunErrors::MUST_FILTER_ON_ENTRY_OR_CATEGORY
	 * @return VidiunCategoryEntryListResponse
	 */
	function listAction(VidiunCategoryEntryFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunCategoryEntryFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * Index CategoryEntry by Id
	 * 
	 * @action index
	 * @param string $entryId
	 * @param int $categoryId
	 * @param bool $shouldUpdate
	 * @throws VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY
	 * @return int
	 */
	function indexAction($entryId, $categoryId, $shouldUpdate = true)
	{
		if(vEntitlementUtils::getEntitlementEnforcement())
			throw new VidiunAPIException(VidiunErrors::CANNOT_INDEX_OBJECT_WHEN_ENTITLEMENT_IS_ENABLE);
		
		$dbCategoryEntry = categoryEntryPeer::retrieveByCategoryIdAndEntryId($categoryId, $entryId);
		if(!$dbCategoryEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY);
			
		if (!$shouldUpdate)
		{
			$dbCategoryEntry->setUpdatedAt(time());
			$dbCategoryEntry->save();
			
			return $dbCategoryEntry->getIntId();
		}
		
		$dbCategoryEntry->reSetCategoryFullIds();
		$dbCategoryEntry->save();
		
		
		$entry = entryPeer::retrieveByPK($dbCategoryEntry->getEntryId());	
		if($entry)
		{
			$categoryEntries = categoryEntryPeer::retrieveActiveByEntryId($entryId);
			
			$categoriesIds = array();
			foreach($categoryEntries as $categoryEntry)
			{
				$categoriesIds[] = $categoryEntry->getCategoryId();
			}
			
			$categories = categoryPeer::retrieveByPKs($categoriesIds);
			
			$isCategoriesModified = false;
			$categoriesFullName = array();
			foreach($categories as $category)
			{
				if($category->getPrivacyContexts() == null)
				{
					$categoriesFullName[] = $category->getFullName();
					$isCategoriesModified = true;
				}
			} 
				
			$entry->setCategories(implode(',', $categoriesFullName));
			categoryEntryPeer::syncEntriesCategories($entry, $isCategoriesModified);
			$entry->save();
		}
		
		return $dbCategoryEntry->getId();
				
	}

	private static function applyStatusOnChildren($dbEntry, $categoryId, $status)
	{
		$relatedEntries = entryPeer::retrieveChildEntriesByEntryIdAndPartnerId($dbEntry->getId(), $dbEntry->getPartnerId());
		foreach ($relatedEntries as $relatedEntry)
		{
			$dbCategoryEntry = categoryEntryPeer::retrieveByCategoryIdAndEntryIdNotRejected($categoryId, $relatedEntry->getId());
			if($dbCategoryEntry)
			{
				$dbCategoryEntry->setStatus($status);
				$dbCategoryEntry->save();
			}
		}
	}

	/**
	 * activate CategoryEntry when it is pending moderation
	 * 
	 * @action activate
	 * @param string $entryId
	 * @param int $categoryId
	 * @throws VidiunErrors::INVALID_ENTRY_ID
	 * @throws VidiunErrors::CATEGORY_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY
	 * @throws VidiunErrors::CANNOT_ACTIVATE_CATEGORY_ENTRY
	 */
	function activateAction($entryId, $categoryId)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if (!$entry)
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID, $entryId);
			
		$category = categoryPeer::retrieveByPK($categoryId);
		if (!$category)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $categoryId);
		
		$dbCategoryEntry = categoryEntryPeer::retrieveByCategoryIdAndEntryIdNotRejected($categoryId, $entryId);
		if(!$dbCategoryEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY);
			
		//validate user is entiteld to activate entry from category 
		if(vEntitlementUtils::getEntitlementEnforcement())
		{
			$categoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($categoryId, vCurrentContext::getCurrentVsVuserId());
			if(!$categoryVuser || 
				($categoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MANAGER && 
				 $categoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MODERATOR))
					throw new VidiunAPIException(VidiunErrors::CANNOT_ACTIVATE_CATEGORY_ENTRY);
					
		}
		
		if (vCurrentContext::getCurrentVsVuserId() == $dbCategoryEntry->getCreatorVuserId() &&
			$this->getPartner()->getEnabledService(VidiunPermissionName::FEATURE_BLOCK_CATEGORY_MODERATION_SELF_APPROVE))
		{
			throw new VidiunAPIException(VidiunErrors::CANNOT_ACTIVATE_CATEGORY_ENTRY);
		}

		if($dbCategoryEntry->getStatus() != CategoryEntryStatus::PENDING)
			throw new VidiunAPIException(VidiunErrors::CANNOT_ACTIVATE_CATEGORY_ENTRY_SINCE_IT_IS_NOT_PENDING);
			
		$dbCategoryEntry->setStatus(CategoryEntryStatus::ACTIVE);
		$dbCategoryEntry->save();

		self::applyStatusOnChildren($entry, $categoryId, CategoryEntryStatus::ACTIVE);
	}

	/**
	 * activate CategoryEntry when it is pending moderation
	 *
	 * @action reject
	 * @param string $entryId
	 * @param int $categoryId
	 * @throws VidiunErrors::INVALID_ENTRY_ID
	 * @throws VidiunErrors::CATEGORY_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY
	 * @throws VidiunErrors::CANNOT_ACTIVATE_CATEGORY_ENTRY
	 */
	function rejectAction($entryId, $categoryId)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if (!$entry)
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID, $entryId);
			
		$category = categoryPeer::retrieveByPK($categoryId);
		if (!$category)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $categoryId);
		
		$dbCategoryEntry = categoryEntryPeer::retrieveByCategoryIdAndEntryId($categoryId, $entryId);
		if(!$dbCategoryEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY);
			
		//validate user is entiteld to reject entry from category 
		if(vEntitlementUtils::getEntitlementEnforcement())
		{
			$categoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($categoryId, vCurrentContext::getCurrentVsVuserId());
			if(!$categoryVuser || 
				($categoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MANAGER && 
				 $categoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MODERATOR))
					throw new VidiunAPIException(VidiunErrors::CANNOT_REJECT_CATEGORY_ENTRY);
					
		}
			
		if (vCurrentContext::getCurrentVsVuserId() == $dbCategoryEntry->getCreatorVuserId() &&
			$this->getPartner()->getEnabledService(VidiunPermissionName::FEATURE_BLOCK_CATEGORY_MODERATION_SELF_APPROVE))
		{
			throw new VidiunAPIException(VidiunErrors::CANNOT_REJECT_CATEGORY_ENTRY);
		}

		if($dbCategoryEntry->getStatus() != CategoryEntryStatus::PENDING)
			throw new VidiunAPIException(VidiunErrors::CANNOT_REJECT_CATEGORY_ENTRY_SINCE_IT_IS_NOT_PENDING);
			
		$dbCategoryEntry->setStatus(CategoryEntryStatus::REJECTED);
		$dbCategoryEntry->save();

		self::applyStatusOnChildren($entry, $categoryId, CategoryEntryStatus::REJECTED);
	}
	
	/**
	 * update privacy context from the category
	 * 
	 * @action syncPrivacyContext
	 * @param string $entryId
	 * @param int $categoryId
	 * @throws VidiunErrors::INVALID_ENTRY_ID
	 * @throws VidiunErrors::CATEGORY_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY
	 */
	function syncPrivacyContextAction($entryId, $categoryId)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if (!$entry)
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID, $entryId);
			
		$category = categoryPeer::retrieveByPK($categoryId);
		if (!$category)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $categoryId);
		
		$dbCategoryEntry = categoryEntryPeer::retrieveByCategoryIdAndEntryId($categoryId, $entryId);
		if(!$dbCategoryEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_IS_NOT_ASSIGNED_TO_CATEGORY);
		
		$dbCategoryEntry->setPrivacyContext($category->getPrivacyContexts());
		$dbCategoryEntry->save();
	}
}

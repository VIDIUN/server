<?php

/**
 * Add & Manage CategoryUser - membership of a user in a category
 *
 * @service categoryUser
 */
class CategoryUserService extends VidiunBaseService
{
	/**
	 * Add new CategoryUser
	 * 
	 * @action add
	 * @param VidiunCategoryUser $categoryUser
	 * @return VidiunCategoryUser
	 */
	function addAction(VidiunCategoryUser $categoryUser)
	{
		$dbCategoryVuser = $categoryUser->toInsertableObject();
		/* @var $dbCategoryVuser categoryVuser */
		$category = categoryPeer::retrieveByPK($categoryUser->categoryId);
		if (!$category)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $categoryUser->categoryId);

		$maxUserPerCategory=vConf::get('max_users_per_category');
		if($category->getMembersCount() >= $maxUserPerCategory)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_MAX_USER_REACHED,$maxUserPerCategory);

		$currentVuserCategoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($categoryUser->categoryId, vCurrentContext::getCurrentVsVuserId());
		if (!vEntitlementUtils::getEntitlementEnforcement())
		{
			$dbCategoryVuser->setStatus(CategoryVuserStatus::ACTIVE);	
			$dbCategoryVuser->setPermissionLevel($categoryUser->permissionLevel);
		}
		elseif ($currentVuserCategoryVuser && $currentVuserCategoryVuser->getPermissionLevel() == CategoryVuserPermissionLevel::MANAGER)
		{
			//Current Vuser is manager
			$dbCategoryVuser->setStatus(CategoryVuserStatus::ACTIVE);
		}
		elseif ($category->getUserJoinPolicy() == UserJoinPolicyType::AUTO_JOIN)
		{
			$dbCategoryVuser->setPermissionLevel($category->getDefaultPermissionLevel());
			$dbCategoryVuser->setStatus(CategoryVuserStatus::ACTIVE);
		}
		elseif ($category->getUserJoinPolicy() == UserJoinPolicyType::REQUEST_TO_JOIN)
		{
			$dbCategoryVuser->setPermissionLevel($category->getDefaultPermissionLevel());
			$dbCategoryVuser->setStatus(CategoryVuserStatus::PENDING);
		}
		else
		{
			throw new VidiunAPIException(VidiunErrors::CATEGORY_USER_JOIN_NOT_ALLOWED, $categoryUser->categoryId);	
		}
				
		$dbCategoryVuser->setCategoryFullIds($category->getFullIds());
		$dbCategoryVuser->setPartnerId($this->getPartnerId());
		$dbCategoryVuser->save();
		
		$categoryUser->fromObject($dbCategoryVuser, $this->getResponseProfile());
		return $categoryUser;
	}
	
	/**
	 * Get CategoryUser by id
	 * 
	 * @action get
	 * @param int $categoryId
	 * @param string $userId
	 * @return VidiunCategoryUser
	 */
	function getAction($categoryId, $userId)
	{
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $userId);
		if (!$vuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
			
		$category = categoryPeer::retrieveByPK($categoryId);
		if (!$category)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $categoryId);						

		if($category->getInheritanceType() == InheritanceType::INHERIT)
			$categoryId = $category->getInheritedParentId();
					
		$dbCategoryVuser = categoryVuserPeer::retrieveByCategoryIdAndVuserId($categoryId, $vuser->getId());
		if (!$dbCategoryVuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_CATEGORY_USER_ID, $categoryId, $userId);
			
		$categoryUser = new VidiunCategoryUser();
		$categoryUser->fromObject($dbCategoryVuser, $this->getResponseProfile());
		
		return $categoryUser;
	}
	
	/**
	 * Update CategoryUser by id
	 * 
	 * @action update
	 * @param int $categoryId
	 * @param string $userId
	 * @param VidiunCategoryUser $categoryUser
	 * @param bool $override - to override manual changes
	 * @return VidiunCategoryUser
	 */
	function updateAction($categoryId, $userId, VidiunCategoryUser $categoryUser, $override = false)
	{
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $userId);
		if (!$vuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
		
		$dbCategoryVuser = categoryVuserPeer::retrieveByCategoryIdAndVuserId($categoryId, $vuser->getId());
		if (!$dbCategoryVuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_CATEGORY_USER_ID, $categoryId, $userId);
			
		if(!$override && 
			($categoryUser->updateMethod == null || $categoryUser->updateMethod == VidiunUpdateMethodType::AUTOMATIC) && 
			$dbCategoryVuser->getUpdateMethod() == VidiunUpdateMethodType::MANUAL)
			throw new VidiunAPIException(VidiunErrors::CANNOT_OVERRIDE_MANUAL_CHANGES);
		
		$dbCategoryVuser = $categoryUser->toUpdatableObject($dbCategoryVuser);
				
		$dbCategoryVuser->save();
		
		$categoryUser->fromObject($dbCategoryVuser, $this->getResponseProfile());
		return $categoryUser;
		
	}
	
	/**
	 * Delete a CategoryUser
	 * 
	 * @action delete
	 * @param int $categoryId
	 * @param string $userId
	 */
	function deleteAction($categoryId, $userId)
	{
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $userId);
		
		if (!$vuser)
		{	
			if (vCurrentContext::$master_partner_id != Partner::BATCH_PARTNER_ID)
				throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
			
			vuserPeer::setUseCriteriaFilter(false);
			$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $userId);
			vuserPeer::setUseCriteriaFilter(true);
			
			if (!$vuser)
				throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
		}
			
		$dbCategoryVuser = categoryVuserPeer::retrieveByCategoryIdAndVuserId($categoryId, $vuser->getId());
		/* @var $dbCategoryVuser categoryVuser */
		if (!$dbCategoryVuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_CATEGORY_USER_ID, $categoryId, $userId);
			
		$category = categoryPeer::retrieveByPK($categoryId);
		if (!$category)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $categoryId);						

		if ($category->getInheritanceType() == InheritanceType::INHERIT && vCurrentContext::$master_partner_id != Partner::BATCH_PARTNER_ID)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_INHERIT_MEMBERS, $categoryId);		
		
		// only manager can remove memnger or users remove himself
		$currentVuserCategoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($dbCategoryVuser->getCategoryId());
		if((!$currentVuserCategoryVuser || 
			($currentVuserCategoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MANAGER &&
			 vCurrentContext::$vs_uid != $userId)) &&
			 vCurrentContext::$vs_partner_id != Partner::BATCH_PARTNER_ID &&
			 vEntitlementUtils::getEntitlementEnforcement())
			throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_CATEGORY_USER);
		
		if($dbCategoryVuser->getVuserId() == $category->getVuserId() &&
			vCurrentContext::$vs_partner_id != Partner::BATCH_PARTNER_ID)
			throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_CATEGORY_USER_OWNER);
			
		$dbCategoryVuser->setStatus(CategoryVuserStatus::DELETED);
		$dbCategoryVuser->save();
	} 
	
	/**
	 * activate CategoryUser
	 * 
	 * @action activate
	 * @param int $categoryId
	 * @param string $userId
	 * @return VidiunCategoryUser
	 */
	function activateAction($categoryId, $userId)
	{
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $userId);
		if (!$vuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
			
		$dbCategoryVuser = categoryVuserPeer::retrieveByCategoryIdAndVuserId($categoryId, $vuser->getId());
		if (!$dbCategoryVuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_CATEGORY_USER_ID, $categoryId, $userId);
		
		$currentVuserCategoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($dbCategoryVuser->getCategoryId(), vCurrentContext::getCurrentVsVuserId());
		if(vEntitlementUtils::getEntitlementEnforcement() &&
			(!$currentVuserCategoryVuser || $currentVuserCategoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MANAGER))
			throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_CATEGORY_USER);
		
		$dbCategoryVuser->setStatus(CategoryVuserStatus::ACTIVE);
		$dbCategoryVuser->save();
		
		$categoryUser = new VidiunCategoryUser();
		$categoryUser->fromObject($dbCategoryVuser, $this->getResponseProfile());
		return $categoryUser;
	} 
	
	/**
	 * reject CategoryUser
	 * 
	 * @action deactivate
	 * @param int $categoryId
	 * @param string $userId
	 * @return VidiunCategoryUser
	 */
	function deactivateAction($categoryId, $userId)
	{
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $userId);
		if (!$vuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
			
		$dbCategoryVuser = categoryVuserPeer::retrieveByCategoryIdAndVuserId($categoryId, $vuser->getId());
		if (!$dbCategoryVuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_CATEGORY_USER_ID, $categoryId, $userId);
		
		$currentVuserCategoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($dbCategoryVuser->getCategoryId(), vCurrentContext::getCurrentVsVuserId());
		if(vEntitlementUtils::getEntitlementEnforcement() &&
			(!$currentVuserCategoryVuser || 
			($currentVuserCategoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MANAGER &&
			 vCurrentContext::$vs_uid != $userId)))
			throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_CATEGORY_USER);
		
		$dbCategoryVuser->setStatus(CategoryVuserStatus::NOT_ACTIVE);
		$dbCategoryVuser->save();
		
		$categoryUser = new VidiunCategoryUser();
		$categoryUser->fromObject($dbCategoryVuser, $this->getResponseProfile());
		return $categoryUser;
	} 
	
	
	/**
	 * List all categories
	 * 
	 * @action list
	 * @param VidiunCategoryUserFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunCategoryUserListResponse
	 * @throws VidiunErrors::MUST_FILTER_USERS_OR_CATEGORY
	 */
	function listAction(VidiunCategoryUserFilter $filter = null, VidiunFilterPager $pager = null)
	{	
		if (!$filter || !($filter->categoryIdEqual || $filter->categoryIdIn || $filter->categoryFullIdsStartsWith || $filter->categoryFullIdsEqual || $filter->userIdIn || $filter->userIdEqual || $filter->relatedGroupsByUserId))
			throw new VidiunAPIException(VidiunErrors::MUST_FILTER_USERS_OR_CATEGORY);	
			
		if(!$pager)
			$pager = new VidiunFilterPager();		
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * Copy all member from parent category
	 * 
	 * @action copyFromCategory
	 * @param int $categoryId
	 */
	public function copyFromCategoryAction($categoryId)
	{
		if (vEntitlementUtils::getEntitlementEnforcement())
			throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_CATEGORY_USER);
		
		$categoryDb = categoryPeer::retrieveByPK($categoryId);
		if (!$categoryDb)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $categoryId);

		if($categoryDb->getParentId() == null)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_DOES_NOT_HAVE_PARENT_CATEGORY);
		
		$categoryDb->copyCategoryUsersFromParent($categoryDb->getParentId());
	}
	
	/**
	 * Index CategoryUser by userid and category id
	 * 
	 * @action index
	 * @param string $userId
	 * @param int $categoryId
	 * @param bool $shouldUpdate
	 * @throws VidiunErrors::INVALID_CATEGORY_USER_ID
	 * @return int
	 */
	public function indexAction($userId, $categoryId, $shouldUpdate = true)
	{
		if(vEntitlementUtils::getEntitlementEnforcement())
			throw new VidiunAPIException(VidiunErrors::CANNOT_INDEX_OBJECT_WHEN_ENTITLEMENT_IS_ENABLE);
		
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		$vuser = vuserPeer::getActiveVuserByPartnerAndUid($partnerId, $userId);

		if(!$vuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
			
		$dbCategoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($categoryId, $vuser->getId(), null, false);
		if(!$dbCategoryVuser)
			throw new VidiunAPIException(VidiunErrors::INVALID_CATEGORY_USER_ID);
			
		if (!$shouldUpdate)
		{
			$dbCategoryVuser->setUpdatedAt(time());
			$dbCategoryVuser->save();
			
			return $dbCategoryVuser->getId();
		}
				
		$dbCategoryVuser->reSetCategoryFullIds();
		$dbCategoryVuser->reSetScreenName();
		$dbCategoryVuser->save();
		
		return $dbCategoryVuser->getId();
	}
}

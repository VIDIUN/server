<?php
/**
 * @service group
 * @package plugins.group
 * @subpackage api.services
 */

class GroupService extends VidiunBaseUserService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$vuser = vCurrentContext::getCurrentVsVuser();
		if(!$vuser && !vCurrentContext::$is_admin_session)
		{
			throw new VidiunAPIException(VidiunErrors::USER_ID_NOT_PROVIDED_OR_EMPTY);
		}
	}

	/**
	 * Adds a new group (user of type group).
	 *
	 * @action add
	 * @param VidiunGroup $group a new group
	 * @return VidiunGroup The new group
	 *
	 * @throws VidiunErrors::DUPLICATE_USER_BY_ID
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunErrors::UNKNOWN_PARTNER_ID
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::DUPLICATE_USER_BY_LOGIN_ID
	 */
	public function addAction(VidiunGroup $group)
	{
		$group->type = VuserType::GROUP;
		$lockKey = "user_add_" . $this->getPartnerId() . $group->id;
		$ret =  vLock::runLocked($lockKey, array($this, 'adduserImpl'), array($group));
		return $ret;
	}

	/**
	 * Retrieves a group object for a specified group ID.
	 * @action get
	 * @param string $groupId The unique identifier in the partner's system
	 * @return VidiunGroup The specified user object
	 *
	 * @throws VidiunGroupErrors::INVALID_GROUP_ID
	 */
	public function getAction($groupId)
	{
		$dbGroup = $this->getGroup($groupId);

		if (!vCurrentContext::$is_admin_session )//TODO - add validation function to allow access to the group
			throw new VidiunAPIException(VidiunErrors::CANNOT_RETRIEVE_ANOTHER_USER_USING_NON_ADMIN_SESSION, $groupId);


		if (!$dbGroup)
			throw new VidiunAPIException(VidiunGroupErrors::INVALID_GROUP_ID, $groupId);

		$group = new VidiunGroup();
		$group->fromObject($dbGroup, $this->getResponseProfile());

		return $group;
	}

	/**
	 * Delete group by ID
	 *
	 * @action delete
	 * @param string $groupId The unique identifier in the partner's system
	 * @return VidiunGroup The deleted  object
	 * @throws VidiunErrors::INVALID_USER_ID
	 */
	public function deleteAction($groupId)
	{
		$dbGroup = self::getGroup($groupId);
		$dbGroup->setStatus(VidiunUserStatus::DELETED);
		$dbGroup->save();
		$group = new VidiunGroup();
		$group->fromObject($dbGroup, $this->getResponseProfile());
		return $group;
	}

	/**
	 * Update group by ID
	 *
	 * @action update
	 * @param string $groupId The unique identifier in the partner's system
	 * @param VidiunGroup the updated object
	 * @return VidiunGroup The updated  object
	 * @throws VidiunErrors::INVALID_USER_ID
	 */
	public function updateAction($groupId, VidiunGroup $group)
	{
		$dbGroup = self::getGroup($groupId);
		$dbGroup = $group->toUpdatableObject($dbGroup);
		$dbGroup->save();
		$group = new VidiunGroup();
		$group->fromObject($dbGroup, $this->getResponseProfile());
		return $group;
	}

	/**
	 * Lists group  objects that are associated with an account.
	 * Blocked users are listed unless you use a filter to exclude them.
	 * Deleted users are not listed unless you use a filter to include them.
	 *
	 * @action list
	 * @param VidiunGroupFilter $filter
	 * @param VidiunFilterPager $pager A limit for the number of records to display on a page
	 * @return VidiunGroupListResponse The list of user objects
	 */
	public function listAction(VidiunGroupFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunGroupFilter();

		if(!$pager)
			$pager = new VidiunFilterPager();

		return $filter->getListResponse($pager, $this->getResponseProfile());

	}

	protected function getGroup($groupId)
	{
		$dbGroup = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $groupId);
		if(!$dbGroup || $dbGroup->getType() != VuserType::GROUP)
		{
			throw new VidiunAPIException(VidiunGroupErrors::INVALID_GROUP_ID);
		}
		return $dbGroup;
	}

	/**
	 * @action searchGroup
	 * @actionAlias elasticsearch_esearch.searchGroup
	 * @param VidiunESearchGroupParams $searchParams
	 * @param VidiunPager $pager
	 * @return VidiunESearchGroupResponse
	 */
	public function searchGroupAction(VidiunESearchGroupParams $searchParams, VidiunPager $pager = null)
	{
		$userSearch = new vUserSearch();
		list($coreResults, $objectCount) = self::initAndSearch($userSearch, $searchParams, $pager);
		$response = new VidiunESearchGroupResponse();
		$response->objects = VidiunESearchGroupResultArray::fromDbArray($coreResults, $this->getResponseProfile());
		$response->totalCount = $objectCount;
		return $response;
	}

	/**
	 * @param vBaseSearch $coreSearchObject
	 * @param $searchParams
	 * @param $pager
	 * @return array
	 */
	protected function initAndSearch($coreSearchObject, $searchParams, $pager)
	{
		list($coreSearchOperator, $objectStatusesArr, $objectId, $vPager, $coreOrder) =
			self::initSearchActionParams($searchParams, $pager);
		$elasticResults = $coreSearchObject->doSearch($coreSearchOperator, $vPager, $objectStatusesArr, $objectId, $coreOrder);

		list($coreResults, $objectCount) = vESearchCoreAdapter::transformElasticToCoreObject($elasticResults, $coreSearchObject);
		return array($coreResults, $objectCount);
	}

	protected static function initSearchActionParams($searchParams, VidiunPager $pager = null)
	{

		/**
		 * @var ESearchParams $coreParams
		 */
		$coreParams = $searchParams->toObject();

		$groupTypeItem = new ESearchUserItem();
		$groupTypeItem->setSearchTerm(VuserType::GROUP);
		$groupTypeItem->setItemType(ESearchItemType::EXACT_MATCH);
		$groupTypeItem->setFieldName(ESearchUserFieldName::TYPE);

		$baseOperator = new ESearchOperator();
		$baseOperator->setOperator(ESearchOperatorType::AND_OP);
		$baseOperator->setSearchItems(array($coreParams->getSearchOperator(), $groupTypeItem));

		$objectStatusesArr = array();
		$objectStatuses = $coreParams->getObjectStatuses();
		if (!empty($objectStatuses))
		{
			$objectStatusesArr = explode(',', $objectStatuses);
		}

		$vPager = null;
		if ($pager)
		{
			$vPager = $pager->toObject();
		}

		return array($baseOperator, $objectStatusesArr, $coreParams->getObjectId(), $vPager, $coreParams->getOrderBy());
	}

	/**
	 * clone the group (groupId), and set group id with the neeGroupName.
	 *
	 * @action clone
	 * @param string $originalGroupId The unique identifier in the partner's system
	 * @param string $newGroupName The unique identifier in the partner's system
	 * @return VidiunGroup The cloned group
	 *
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunGroupErrors::INVALID_GROUP_ID
	 */
	public function cloneAction($originalGroupId, $newGroupName)
	{
		$dbGroup = $this->getGroup($originalGroupId);

		if (!$dbGroup)
		{
			throw new VidiunAPIException(VidiunGroupErrors::INVALID_GROUP_ID, $originalGroupId);
		}

		$dbNewGroup = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $newGroupName);
		if ($dbNewGroup)
		{
			throw new VidiunAPIException(VidiunGroupErrors::DUPLICATE_GROUP_BY_ID, $newGroupName);
		}

		$group = new VidiunGroup();
		$newDbGroup = $group->clonedObject($dbGroup, $newGroupName);
		$group->validateForInsert($newDbGroup);
		$newDbGroup->save();

		$groupUsers =  VuserVgroupPeer::retrieveVuserVgroupByVgroupId($dbGroup->getId());
		$vusers = $this->getVusersFromVuserVgroup($groupUsers);
		$GroupUser = new GroupUserService();
		$GroupUser->addGroupUsersToClonedGroup($vusers, $newDbGroup, $dbGroup->getId());

		$group->fromObject($newDbGroup, $this->getResponseProfile());

		return $group;
	}

	protected function getVusersFromVuserVgroup($groupUsers)
	{
		$vusers = array();
		foreach ($groupUsers as $groupUser)
		{
			$vuserId = $groupUser->getVuserId();
			$vusers[] = vuserPeer::retrieveByPK($vuserId);
		}
		return $vusers;
	}
}
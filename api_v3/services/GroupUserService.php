<?php

/**
 * Add & Manage GroupUser
 *
 * @service groupUser
 */
class GroupUserService extends VidiunBaseService
{
	const USER_GROUP_SYNC_THRESHOLD_DEFUALT = '50';

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('VuserVgroup');
	}

	/**
	 * Add new GroupUser
	 *
	 * @action add
	 * @param VidiunGroupUser $groupUser
	 * @return VidiunGroupUser
	 * @throws VidiunAPIException
	 */
	function addAction(VidiunGroupUser $groupUser)
	{
		$this->checkPermissionsForGroupUser($groupUser->groupId);
		/* @var $dbGroupUser VuserVgroup*/
		$partnerId = $this->getPartnerId();

		//verify vuser exists
		$vuser = vuserPeer::getVuserByPartnerAndUid( $partnerId, $groupUser->userId);
		if ( !$vuser || $vuser->getType() != VuserType::USER)
			throw new VidiunAPIException ( VidiunErrors::USER_NOT_FOUND, $groupUser->userId );

		//verify vgroup exists
		$vgroup = vuserPeer::getVuserByPartnerAndUid( $partnerId, $groupUser->groupId);
		if ( !$vgroup || $vgroup->getType() != VuserType::GROUP)
			throw new VidiunAPIException ( VidiunErrors::GROUP_NOT_FOUND, $groupUser->userId );

		//verify vuser does not belongs to vgroup
		$vuserVgroup = VuserVgroupPeer::retrieveByVuserIdAndVgroupId($vuser->getId(), $vgroup->getId());
		if($vuserVgroup)
			throw new VidiunAPIException (VidiunErrors::GROUP_USER_ALREADY_EXISTS);

		//verify user does not belongs to more than max allowed groups
		$criteria = new Criteria();
		$criteria->add(VuserVgroupPeer::VUSER_ID, $vuser->getId());
		$criteria->add(VuserVgroupPeer::STATUS, VuserVgroupStatus::ACTIVE);
		if (VuserVgroupPeer::doCount($criteria) > VuserVgroup::MAX_NUMBER_OF_GROUPS_PER_USER){
			throw new VidiunAPIException (VidiunErrors::USER_EXCEEDED_MAX_GROUPS);
		}

		$numberOfUsersPerGroup = $this->getNumberOfUsersInGroup($vgroup);
		$vgroup->setMembersCount($numberOfUsersPerGroup+1);
		$vgroup->save();

		$dbGroupUser = $groupUser->toInsertableObject();
		$dbGroupUser->setPartnerId($this->getPartnerId());
		$dbGroupUser->setStatus(VuserVgroupStatus::ACTIVE);
		$dbGroupUser->save();
		$groupUser->fromObject($dbGroupUser);

		return $groupUser;
	}

	/**
	 * update GroupUser
	 *
	 * @action update
	 * @param string $groupUserId
	 * @param VidiunGroupUser $groupUser
	 * @return VidiunGroupUser
	 * @throws VidiunAPIException
	 */
	function updateAction($groupUserId, VidiunGroupUser $groupUser)
	{
		$currentDBGroupUser = VuserVgroupPeer::retrieveByPK($groupUserId);
		if (!$currentDBGroupUser)
		{
			throw new VidiunAPIException(VidiunErrors::GROUP_USER_NOT_FOUND);
		}

		$this->checkPermissionsForGroupUser($currentDBGroupUser->getVgroupId());
		$dbGroupUser = $groupUser->toUpdatableObject($currentDBGroupUser);
		$dbGroupUser->save();
		$groupUser = new VidiunGroupUser();
		$groupUser->fromObject($dbGroupUser, $this->getResponseProfile());
		return $groupUser;
	}

	/**
	 * delete by userId and groupId
	 *
	 * @action delete
	 * @param string $userId
	 * @param string $groupId
	 * @throws VidiunAPIException
	 */
	function deleteAction($userId, $groupId)
	{
		$this->checkPermissionsForGroupUser($groupId);
		$partnerId = $this->getPartnerId();
		//verify vuser exists
		$vuser = vuserPeer::getVuserByPartnerAndUid( $partnerId, $userId);
		if (!$vuser)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
		}


		//verify group exists
		$vgroup = vuserPeer::getVuserByPartnerAndUid(  $partnerId, $groupId);
		if (!$vgroup)
		{
			//if the delete worker was triggered due to group deletion
			if(vCurrentContext::$master_partner_id != Partner::BATCH_PARTNER_ID)
			{
				throw new VidiunAPIException(VidiunErrors::GROUP_NOT_FOUND, $groupId);
			}

			vuserPeer::setUseCriteriaFilter(false);
			$vgroup = vuserPeer::getVuserByPartnerAndUid($partnerId, $groupId);
			vuserPeer::setUseCriteriaFilter(true);

			if(!$vgroup)
			{
				throw new VidiunAPIException (VidiunErrors::GROUP_NOT_FOUND, $groupId);
			}
		}


		$dbVuserVgroup = VuserVgroupPeer::retrieveByVuserIdAndVgroupId($vuser->getId(), $vgroup->getId());
		if(!$dbVuserVgroup)
		{
			throw new VidiunAPIException(VidiunErrors::GROUP_USER_DOES_NOT_EXIST, $userId, $groupId);
		}
		$numberOfUsersPerGroup = $this->getNumberOfUsersInGroup($vgroup);
		$vgroup->setMembersCount(max(0,$numberOfUsersPerGroup-1));
		$vgroup->save();

		$dbVuserVgroup->setStatus(VuserVgroupStatus::DELETED);
		$dbVuserVgroup->save();

		$groupUser = new VidiunGroupUser();
		$groupUser->fromObject($dbVuserVgroup);
	}

	/**
	 * List all GroupUsers
	 * 
	 * @action list
	 * @param VidiunGroupUserFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunGroupUserListResponse
	 */
	function listAction(VidiunGroupUserFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
		{
			$filter = new VidiunGroupUserFilter();
		}

		$this->checkPermissionsForList($filter);

		if (!$pager)
		{
			$pager = new VidiunFilterPager();
		}
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	/**
	 * sync by userId and groupIds
	 *
	 * @action sync
	 * @param string $userId
	 * @param string $groupIds
	 * @param bool $removeFromExistingGroups
	 * @param bool $createNewGroups
	 * @return VidiunBulkUpload|null
	 * @throws VidiunAPIException
	 */
	public function syncAction($userId, $groupIds, $removeFromExistingGroups = true, $createNewGroups = true)
	{
		if(strpos($groupIds,';')===false)
		{
			$seperator = ',';
		}
		else
		{
			$seperator = ';';
		}

		$groupIdsList = explode($seperator, $groupIds);
		self::validateSyncGroupUserArgs($userId, $groupIdsList, $groupIds);

		$vUser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $userId);
		if (!$vUser || $vUser->getType() != VuserType::USER)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $userId);
		}

		$groupLimit = vConf::get('user_groups_sync_threshold', 'local', self::USER_GROUP_SYNC_THRESHOLD_DEFUALT);
		$bulkUpload = null;
		$bulkGroupUserSyncCsv = new vBulkGroupUserSyncCsv($vUser, $groupIdsList);
		$shouldHandleGroupsInBatch = ($groupLimit < count($groupIdsList));
		if (!$shouldHandleGroupsInBatch)
		{
			list($groupIdsToRemove, $groupIdsToAdd) = $bulkGroupUserSyncCsv->getSyncGroupUsers($removeFromExistingGroups, $createNewGroups);
			$this->initService('groupuser', 'groupuser', 'add');
			$shouldHandleGroupsInBatch = $this->addUserGroups($userId, $groupIdsToAdd) || !empty($groupIdsToRemove);
		}
		if ($shouldHandleGroupsInBatch)
		{
			$bulkUpload = self::handleGroupUserInBatch($bulkGroupUserSyncCsv, $removeFromExistingGroups, $createNewGroups);
		}

		return $bulkUpload;
	}

	protected function getNumberOfUsersInGroup($group)
	{
		$numberOfUsersPerGroup = $group->getMembersCount();
		if(!$numberOfUsersPerGroup)
		{
			$criteria = new Criteria();
			$criteria->add(VuserVgroupPeer::VGROUP_ID, $group->getId());
			$criteria->add(VuserVgroupPeer::STATUS, VuserVgroupStatus::ACTIVE);
			$numberOfUsersPerGroup = VuserVgroupPeer::doCount($criteria);
		}
		return $numberOfUsersPerGroup;
	}

	protected static function handleGroupUserInBatch(vBulkGroupUserSyncCsv $bulkGroupUserSyncCsv, $removeFromExistingGroups, $createNewGroups)
	{
		$fileData = $bulkGroupUserSyncCsv->getSyncGroupUsersCsvFile($removeFromExistingGroups, $createNewGroups);
		if (!$fileData)
		{
			return null;
		}

		$bulkService = new BulkService();
		$bulkService->initService('bulkupload_bulk', 'bulk', 'addUsers');
		return $bulkService->addUsersAction($fileData);
	}

	/**
	 * @param $userId
	 * @param $groupIdsToAdd
	 * @return bool (true if errors occurred)
	 */
	protected function addUserGroups($userId, $groupIdsToAdd)
	{
		$shouldHandleGroupsInBatch = false;
		foreach ($groupIdsToAdd as $groupId)
		{
			try
			{
				$groupUser = new VidiunGroupUser();
				$groupUser->userId = $userId;
				$groupUser->groupId = $groupId;
				$groupUser->creationMode = VidiunGroupUserCreationMode::AUTOMATIC;
				$this->addAction($groupUser);
			}
			catch (Exception $e)
			{
				$shouldHandleGroupsInBatch = true;
			}
		}
		return $shouldHandleGroupsInBatch;
	}

	public function addGroupUsersToClonedGroup($vusers, $newGroup, $originalGroupId)
	{
		$groupUsersLimit = vConf::get('user_groups_sync_threshold', 'local', self::USER_GROUP_SYNC_THRESHOLD_DEFUALT);
		$bulkGroupUserSyncCsv = new vBulkGroupUsersToGroupCsv($vusers, $newGroup->getPuserId());
		$shouldHandleGroupsUsersInBatch = ($groupUsersLimit < count($vusers));
		if (!$shouldHandleGroupsUsersInBatch)
		{
			$this->initService('groupuser', 'groupuser', 'add');
			list($shouldHandleGroupsUsersInBatch, $userToAddInBulk) = $this->addUserGroupsToGroup($vusers, $newGroup, $originalGroupId);
			$vusers = $userToAddInBulk;
		}
		if ($shouldHandleGroupsUsersInBatch)
		{
			$bulkGroupUserSyncCsv->AddGroupUserInBatch($vusers, $originalGroupId);
		}

	}

	/**
	 * @param $userIdsToAdd
	 * @param $groupId
	 * @return array(bool (true if errors occurred),$usersToAddInBulk - users that we failed while trying to add them to group)
	 */
	public function addUserGroupsToGroup($userToAdd, $group, $originalGroupId)
	{
		$usersToAddInBulk = array();
		$groupId = $group->getPuserId();
		$shouldHandleGroupsInBatch = false;
		foreach ($userToAdd as $user)
		{
			$originalGroupUser = VuserVgroupPeer::retrieveByVuserIdAndVgroupId($user->getId(),$originalGroupId);
			try
			{
				$groupUser = new VidiunGroupUser();
				$groupUser->userId = $user->getPuserId();
				$groupUser->groupId = $groupId;
				$groupUser->creationMode = VidiunGroupUserCreationMode::AUTOMATIC;
				$groupUser->userRole = $originalGroupUser->getUserRole();
				$this->addAction($groupUser);
			}
			catch (Exception $e)
			{
				$shouldHandleGroupsInBatch = true;
				$usersToAddInBulk[] = $user;
			}
		}
		return array($shouldHandleGroupsInBatch, $usersToAddInBulk);
	}

	/**
	 * @param $userId
	 * @param $groupIdsList
	 * @param $groupIds
	 * @throws VidiunAPIException
	 */
	protected static function validateSyncGroupUserArgs($userId, $groupIdsList, $groupIds)
	{
		if (!preg_match(vuser::PUSER_ID_REGEXP, $userId))
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'userId');
		}

		if(!strlen(trim($groupIds)))
		{
			throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, 'groupIds');
		}

		foreach ($groupIdsList as $groupId)
		{
			if (!preg_match(vuser::PUSER_ID_REGEXP, trim($groupId)))
			{
				throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'groupIds');
			}
		}
	}

	protected function throwServiceForbidden()
	{
		$e = new VidiunAPIException ( APIErrors::SERVICE_FORBIDDEN, $this->serviceId.'->'.$this->actionName);
		header("X-Vidiun:error-".$e->getCode());
		header("X-Vidiun-App: exiting on error ".$e->getCode()." - ".$e->getMessage());
		throw $e;
	}

	protected function checkPermissionsForGroupUser($groupId)
	{
		if(!$this->checkPermissionsForGroupUserFromVs() && !self::checkIfVsUserIsGroupManager($groupId))
		{
			$this->throwServiceForbidden();
		}
	}

	public static function checkIfVsUserIsGroupManager($pUserGroupId)
	{
		$vuserId = vCurrentContext::getCurrentVsVuserId();
		if($vuserId)
		{
			$groupUser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::$vs_partner_id, $pUserGroupId);
			if($groupUser)
			{
				$vsUserGroup = VuserVgroupPeer::retrieveByVuserIdAndVgroupId($vuserId, $groupUser->getVuserId());
				if ($vsUserGroup && $vsUserGroup->getUserRole() == GroupUserRole::MANAGER)
				{
					return true;
				}
			}
		}

		return false;
	}

	protected function checkPermissionsForGroupUserFromVs()
	{
		return (vCurrentContext::$is_admin_session || vCurrentContext::$vs_partner_id == Partner::BATCH_PARTNER_ID ||
			vPermissionManager::isPermitted("CONTENT_MANAGE_ASSIGN_USER_GROUP"));
	}

	/**
	 * @param VidiunGroupUserFilter $filter
	 * @throws VidiunAPIException
	 */
	protected function checkPermissionsForList($filter)
	{
		if(!$this->checkPermissionsForGroupUserFromVs())
		{
			if($filter->groupIdEqual == null && $filter->userIdEqual == null)
			{
				throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL,
					$filter->getFormattedPropertyNameWithClassName('userIdEqual') .
					'/' . $this->getFormattedPropertyNameWithClassName('groupIdEqual'));
			}
			else if($filter->userIdEqual != null)
			{
				$vuser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $filter->userIdEqual);
				if($vuser->getVuserId() != vCurrentContext::getCurrentVsVuserId())
				{
					$this->throwServiceForbidden();
				}
			}
			else if(!self::checkIfVsUserIsGroupManager($filter->groupIdEqual))
			{
				$this->throwServiceForbidden();
			}
		}
	}
}

<?php

/**
 * UserRole service lets you create and manage user roles
 * @service userRole
 * @package api
 * @subpackage services
 */
class UserRoleService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		$this->applyPartnerFilterForClass('UserRole');
		$this->applyPartnerFilterForClass('Permission');
		$this->applyPartnerFilterForClass('PermissionItem');
	}
	
	protected function globalPartnerAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		if ($actionName === 'list') {
			return true;
		}
		if ($actionName === 'clone') {
			return true;
		}
		return parent::globalPartnerAllowed($actionName);
	}

	
	/**
	 * Adds a new user role object to the account.
	 * 
	 * @action add
	 * @param VidiunUserRole $userRole A new role
	 * @return VidiunUserRole The added user role object
	 * 
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws VidiunErrors::PROPERTY_VALIDATION_NOT_UPDATABLE
	 * @throws VidiunErrors::PERMISSION_NOT_FOUND
	 */
	public function addAction(VidiunUserRole $userRole)
	{
		$userRole->validatePropertyNotNull('name');
		
		if (!$userRole->status) {
			$userRole->status = VidiunUserRoleStatus::ACTIVE;
		}
		
		// cannot add a role with a name that already exists
		if (UserRolePeer::getByNameAndPartnerId($userRole->name, $this->getPartnerId())) {
			throw new VidiunAPIException(VidiunErrors::ROLE_NAME_ALREADY_EXISTS);
		}
		
		try { PermissionPeer::checkValidPermissionsForRole($userRole->permissionNames, $this->getPartnerId());	}
		catch (vPermissionException $e) {
			$code = $e->getCode();
			if ($code == vPermissionException::PERMISSION_NOT_FOUND) {
				throw new VidiunAPIException(VidiunErrors::PERMISSION_NOT_FOUND, $e->getMessage());
			}
		}
							
		$dbUserRole = $userRole->toInsertableObject();
		$dbUserRole->setPartnerId($this->getPartnerId());
		$dbUserRole->save();
		
		$userRole = new VidiunUserRole();
		$userRole->fromObject($dbUserRole, $this->getResponseProfile());
		
		return $userRole;
	}
	
	/**
	 * Retrieves a user role object using its ID.
	 * 
	 * @action get
	 * @param int $userRoleId The user role's unique identifier
	 * @return VidiunUserRole The retrieved user role object
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function getAction($userRoleId)
	{
		$dbUserRole = UserRolePeer::retrieveByPK($userRoleId);
		
		if (!$dbUserRole) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $userRoleId);
		}
			
		$userRole = new VidiunUserRole();
		$userRole->fromObject($dbUserRole, $this->getResponseProfile());
		
		return $userRole;
	}
	

	/**
	 * Updates an existing user role object.
	 * 
	 * @action update
	 * @param int $userRoleId The user role's unique identifier
	 * @param VidiunUserRole $userRole The user role's unique identifier
	 * @return VidiunUserRole The updated user role object
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @throws VidiunErrors::PERMISSION_NOT_FOUND
	 */	
	public function updateAction($userRoleId, VidiunUserRole $userRole)
	{
		/* critera is used here instead of retrieveByPk on purpose!
		   if the current context is assigned to a partner 0 role, then retrieveByPk will return it from cache even though partner 0 is not in
		   the partner group for the current action and context */
		$c = new Criteria();
		$c->addAnd(UserRolePeer::ID, $userRoleId, Criteria::EQUAL);
		if ($this->partnerGroup() != myPartnerUtils::ALL_PARTNERS_WILD_CHAR) {
			$c->addAnd(UserRolePeer::PARTNER_ID, explode(',',$this->partnerGroup()), Criteria::IN);
		}
		$dbUserRole = UserRolePeer::doSelectOne($c);
	
		if (!$dbUserRole) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $userRoleId);
		}
		
		// cannot update name to a name that already exists
		if ($userRole->name && $userRole->name != $dbUserRole->getName()) {
			if (UserRolePeer::getByNameAndPartnerId($userRole->name, $this->getPartnerId())) {
				throw new VidiunAPIException(VidiunErrors::ROLE_NAME_ALREADY_EXISTS);
			}
		}
		if (!is_null($userRole->permissionNames) && !($userRole->permissionNames instanceof VidiunNullField)) {
			try { PermissionPeer::checkValidPermissionsForRole($userRole->permissionNames, $this->getPartnerId());	}
			catch (vPermissionException $e) {
				$code = $e->getCode();
				if ($code == vPermissionException::PERMISSION_NOT_FOUND) {
					throw new VidiunAPIException(VidiunErrors::PERMISSION_NOT_FOUND, $e->getMessage());
				}
			}
		}
		
		$dbUserRole = $userRole->toUpdatableObject($dbUserRole);
		$dbUserRole->save();
	
		$userRole = new VidiunUserRole();
		$userRole->fromObject($dbUserRole, $this->getResponseProfile());
		
		return $userRole;
	}

	/**
	 * Deletes an existing user role object.
	 * 
	 * @action delete
	 * @param int $userRoleId The user role's unique identifier
	 * @return VidiunUserRole The deleted user role object
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @throws VidiunErrors::ROLE_IS_BEING_USED
	 */		
	public function deleteAction($userRoleId)
	{
		$dbUserRole = UserRolePeer::retrieveByPK($userRoleId);
	
		if (!$dbUserRole || $dbUserRole->getPartnerId() == PartnerPeer::GLOBAL_PARTNER) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $userRoleId);
		}

		try {
			$dbUserRole->setAsDeleted();
		}
		catch (vPermissionException $e) {
			$code = $e->getCode();
			if ($code == vPermissionException::ROLE_IS_BEING_USED) {
				throw new VidiunAPIException(VidiunErrors::ROLE_IS_BEING_USED);
			}
			throw $e;			
		}	
		$dbUserRole->save();
			
		$userRole = new VidiunUserRole();
		$userRole->fromObject($dbUserRole, $this->getResponseProfile());
		
		return $userRole;
	}
	
	/**
	 * Lists user role objects that are associated with an account.
	 * Blocked user roles are listed unless you use a filter to exclude them.
	 * Deleted user roles are not listed unless you use a filter to include them.
	 * 
	 * @action list
	 * @param VidiunUserRoleFilter $filter A filter used to exclude specific types of user roles
	 * @param VidiunFilterPager $pager A limit for the number of records to display on a page
	 * @return VidiunUserRoleListResponse The list of user role objects
	 */
	public function listAction(VidiunUserRoleFilter  $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunUserRoleFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * Creates a new user role object that is a duplicate of an existing role.
	 * 
	 * @action clone
	 * @param int $userRoleId The user role's unique identifier
	 * @return VidiunUserRole The duplicate user role object
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */
	public function cloneAction($userRoleId)
	{
		$dbUserRole = UserRolePeer::retrieveByPK($userRoleId);
	
		if ( !$dbUserRole || $dbUserRole->getStatus() == UserRoleStatus::DELETED ||
		     ($dbUserRole->getPartnerId() != PartnerPeer::GLOBAL_PARTNER && $dbUserRole->getPartnerId() != $this->getPartnerId()) )
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $userRoleId);
		}
		
		$newDbRole = $dbUserRole->copyToPartner($this->getPartnerId());
		$newName = $newDbRole->getName(). ' copy ('.date("D j M o, H:i:s").')';
		$newDbRole->setName($newName);
		$newDbRole->save();
		
		$userRole = new VidiunUserRole();
		$userRole->fromObject($newDbRole, $this->getResponseProfile());
		
		return $userRole;
	}
}

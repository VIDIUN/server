<?php

/**
 * Manage access control profiles
 *
 * @service accessControlProfile
 */
class AccessControlProfileService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('accessControl'); 	
	}
	
	/**
	 * Add new access control profile
	 * 
	 * @action add
	 * @param VidiunAccessControlProfile $accessControlProfile
	 * @return VidiunAccessControlProfile
	 */
	function addAction(VidiunAccessControlProfile $accessControlProfile)
	{
		$dbAccessControl = $accessControlProfile->toInsertableObject();
		$dbAccessControl->setPartnerId($this->getPartnerId());
		$dbAccessControl->save();
		
		$accessControlProfile = new VidiunAccessControlProfile();
		$accessControlProfile->fromObject($dbAccessControl, $this->getResponseProfile());
		return $accessControlProfile;
	}
	
	/**
	 * Get access control profile by id
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunAccessControlProfile
	 * 
	 * @throws VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbAccessControl = accessControlPeer::retrieveByPK($id);
		if (!$dbAccessControl)
			throw new VidiunAPIException(VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND, $id);
			
		$accessControlProfile = new VidiunAccessControlProfile();
		$accessControlProfile->fromObject($dbAccessControl, $this->getResponseProfile());
		return $accessControlProfile;
	}
	
	/**
	 * Update access control profile by id
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunAccessControlProfile $accessControlProfile
	 * @return VidiunAccessControlProfile
	 * 
	 * @throws VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND
	 */
	function updateAction($id, VidiunAccessControlProfile $accessControlProfile)
	{
		$dbAccessControl = accessControlPeer::retrieveByPK($id);
		if (!$dbAccessControl)
			throw new VidiunAPIException(VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND, $id);
		
		$accessControlProfile->toUpdatableObject($dbAccessControl);
		$dbAccessControl->save();
		
		$accessControlProfile = new VidiunAccessControlProfile();
		$accessControlProfile->fromObject($dbAccessControl, $this->getResponseProfile());
		return $accessControlProfile;
	}
	
	/**
	 * Delete access control profile by id
	 * 
	 * @action delete
	 * @param int $id
	 * 
	 * @throws VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND
	 * @throws VidiunErrors::CANNOT_DELETE_DEFAULT_ACCESS_CONTROL
	 */
	function deleteAction($id)
	{
		$dbAccessControl = accessControlPeer::retrieveByPK($id);
		if (!$dbAccessControl)
			throw new VidiunAPIException(VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND, $id);

		if ($dbAccessControl->getIsDefault())
			throw new VidiunAPIException(VidiunErrors::CANNOT_DELETE_DEFAULT_ACCESS_CONTROL);
			
		$dbAccessControl->setDeletedAt(time());
		try
		{
			$dbAccessControl->save();
		}
		catch(vCoreException $e)
		{
			$code = $e->getCode();
			switch($code)
			{
				case vCoreException::EXCEEDED_MAX_ENTRIES_PER_ACCESS_CONTROL_UPDATE_LIMIT :
					throw new VidiunAPIException(VidiunErrors::EXCEEDED_ENTRIES_PER_ACCESS_CONTROL_FOR_UPDATE, $id);
				case vCoreException::NO_DEFAULT_ACCESS_CONTROL :
					throw new VidiunAPIException(VidiunErrors::CANNOT_TRANSFER_ENTRIES_TO_ANOTHER_ACCESS_CONTROL_OBJECT);
				default:
					throw $e;
			}
		}
	}
	
	/**
	 * List access control profiles by filter and pager
	 * 
	 * @action list
	 * @param VidiunFilterPager $filter
	 * @param VidiunAccessControlProfileFilter $pager
	 * @return VidiunAccessControlProfileListResponse
	 */
	function listAction(VidiunAccessControlProfileFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunAccessControlProfileFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
}
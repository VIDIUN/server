<?php

/**
 * Add & Manage Access Controls
 *
 * @service accessControl
 * @deprecated use accessControlProfile service instead
 */
class AccessControlService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('accessControl'); 	
	}
	
	/**
	 * Add new Access Control Profile
	 * 
	 * @action add
	 * @param VidiunAccessControl $accessControl
	 * @return VidiunAccessControl
	 */
	function addAction(VidiunAccessControl $accessControl)
	{
		$accessControl->validatePropertyMinLength("name", 1);
		$accessControl->partnerId = $this->getPartnerId();
		
		$dbAccessControl = new accessControl();
		$accessControl->toObject($dbAccessControl);
		$dbAccessControl->save();
		
		$accessControl = new VidiunAccessControl();
		$accessControl->fromObject($dbAccessControl, $this->getResponseProfile());
		return $accessControl;
	}
	
	/**
	 * Get Access Control Profile by id
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunAccessControl
	 */
	function getAction($id)
	{
		$dbAccessControl = accessControlPeer::retrieveByPK($id);
		if (!$dbAccessControl)
			throw new VidiunAPIException(VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND, $id);
			
		$accessControl = new VidiunAccessControl();
		$accessControl->fromObject($dbAccessControl, $this->getResponseProfile());
		return $accessControl;
	}
	
	/**
	 * Update Access Control Profile by id
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunAccessControl $accessControl
	 * @return VidiunAccessControl
	 * 
	 * @throws VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND
	 * @throws VidiunErrors::ACCESS_CONTROL_NEW_VERSION_UPDATE
	 */
	function updateAction($id, VidiunAccessControl $accessControl)
	{
		$dbAccessControl = accessControlPeer::retrieveByPK($id);
		if (!$dbAccessControl)
			throw new VidiunAPIException(VidiunErrors::ACCESS_CONTROL_ID_NOT_FOUND, $id);
	
		$rules = $dbAccessControl->getRulesArray();
		foreach($rules as $rule)
		{
			if(!($rule instanceof vAccessControlRestriction))
				throw new VidiunAPIException(VidiunErrors::ACCESS_CONTROL_NEW_VERSION_UPDATE, $id);
		}
		
		$accessControl->validatePropertyMinLength("name", 1, true);
			
		$accessControl->toUpdatableObject($dbAccessControl);
		$dbAccessControl->save();
		
		$accessControl = new VidiunAccessControl();
		$accessControl->fromObject($dbAccessControl, $this->getResponseProfile());
		return $accessControl;
	}
	
	/**
	 * Delete Access Control Profile by id
	 * 
	 * @action delete
	 * @param int $id
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
	 * List Access Control Profiles by filter and pager
	 * 
	 * @action list
	 * @param VidiunFilterPager $filter
	 * @param VidiunAccessControlFilter $pager
	 * @return VidiunAccessControlListResponse
	 */
	function listAction(VidiunAccessControlFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunAccessControlFilter();
			
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());  
	}
}
<?php

/**
 * PermissionItem service lets you create and manage permission items
 * @service permissionItem
 * @package api
 * @subpackage services
 */
class PermissionItemService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		self::applyPartnerFilterForClass('Permission');
		self::applyPartnerFilterForClass('PermissionItem');
	}
	
	protected function globalPartnerAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		if ($actionName === 'list') {
			return true;
		}
		return parent::globalPartnerAllowed($actionName);
	}
	
	/**
	 * Adds a new permission item object to the account.
	 * This action is available only to Vidiun system administrators.
	 * 
	 * @action add
	 * @param VidiunPermissionItem $permissionItem The new permission item
	 * @return VidiunPermissionItem The added permission item object
	 * 
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws VidiunErrors::PROPERTY_VALIDATION_NOT_UPDATABLE
	 */
	public function addAction(VidiunPermissionItem $permissionItem)
	{							    
	    $dbPermissionItem = $permissionItem->toInsertableObject(null, array('type'));
	    $dbPermissionItem->setPartnerId($this->getPartnerId());
		$dbPermissionItem->save();
		
		$permissionItem = new VidiunPermissionItem();
		$permissionItem->fromObject($dbPermissionItem, $this->getResponseProfile());
		
		return $permissionItem;
	}
	
	/**
	 * Retrieves a permission item object using its ID.
	 * 
	 * @action get
	 * @param int $permissionItemId The permission item's unique identifier
	 * @return VidiunPermissionItem The retrieved permission item object
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function getAction($permissionItemId)
	{
		$dbPermissionItem = PermissionItemPeer::retrieveByPK($permissionItemId);
		
		if (!$dbPermissionItem) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $permissionItemId);
		}
			
		if ($dbPermissionItem->getType() == PermissionItemType::API_ACTION_ITEM) {
			$permissionItem = new VidiunApiActionPermissionItem();
		}
		else if ($dbPermissionItem->getType() == PermissionItemType::API_PARAMETER_ITEM) {
			$permissionItem = new VidiunApiParameterPermissionItem();
		}
		else {
			$permissionItem = new VidiunPermissionItem();
		}
		
		$permissionItem->fromObject($dbPermissionItem, $this->getResponseProfile());
		
		return $permissionItem;
	}


	/**
	 * Updates an existing permission item object.
	 * This action is available only to Vidiun system administrators.
	 * 
	 * @action update
	 * @param int $permissionItemId The permission item's unique identifier
	 * @param VidiunPermissionItem $permissionItem The updated permission item parameters
	 * @return VidiunPermissionItem The updated permission item object
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */	
	public function updateAction($permissionItemId, VidiunPermissionItem $permissionItem)
	{
		$dbPermissionItem = PermissionItemPeer::retrieveByPK($permissionItemId);
	
		if (!$dbPermissionItem) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $permissionItemId);
		}
		
		$dbPermissionItem = $permissionItem->toUpdatableObject($dbPermissionItem, array('type'));
		$dbPermissionItem->save();
	
		$permissionItem = new VidiunPermissionItem();
		$permissionItem->fromObject($dbPermissionItem, $this->getResponseProfile());
		
		return $permissionItem;
	}

	/**
	 * Deletes an existing permission item object.
	 * This action is available only to Vidiun system administrators.
	 * 
	 * @action delete
	 * @param int $permissionItemId The permission item's unique identifier
	 * @return VidiunPermissionItem The deleted permission item object
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function deleteAction($permissionItemId)
	{
		$dbPermissionItem = PermissionItemPeer::retrieveByPK($permissionItemId);
	
		if (!$dbPermissionItem) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $permissionItemId);
		}
		
		$dbPermissionItem->delete();
			
		$permissionItem = new VidiunPermissionItem();
		$permissionItem->fromObject($dbPermissionItem, $this->getResponseProfile());
		
		return $permissionItem;
	}
	
	/**
	 * Lists permission item objects that are associated with an account.
	 * 
	 * @action list
	 * @param VidiunPermissionItemFilter $filter A filter used to exclude specific types of permission items
	 * @param VidiunFilterPager $pager A limit for the number of records to display on a page
	 * @return VidiunPermissionItemListResponse The list of permission item objects
	 */
	public function listAction(VidiunPermissionItemFilter  $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunPermissionItemFilter();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}	
}

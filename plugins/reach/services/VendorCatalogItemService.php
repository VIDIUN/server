<?php
/**
 * Vendor Catalog Item Service
 *
 * @service vendorCatalogItem
 * @package plugins.reach
 * @subpackage api.services
 * @throws VidiunErrors::SERVICE_FORBIDDEN
 */

class VendorCatalogItemService extends VidiunBaseService
{
	
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		if(!ReachPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, ReachPlugin::PLUGIN_NAME);
		
		$this->applyPartnerFilterForClass('PartnerCatalogItem');
	}
	
	/**
	 * Allows you to add an service catalog item
	 *
	 * @action add
	 * @param VidiunVendorCatalogItem $vendorCatalogItem
	 * @return VidiunVendorCatalogItem
	 */
	public function addAction(VidiunVendorCatalogItem $vendorCatalogItem)
	{
		$dbVendorCatalogItem = $vendorCatalogItem->toInsertableObject();
		
		/* @var $dbVendorCatalogItem VendorCatalogItem */
		$dbVendorCatalogItem->setStatus(VidiunVendorCatalogItemStatus::ACTIVE);
		$dbVendorCatalogItem->save();
		
		// return the saved object
		$vendorCatalogItem = VidiunVendorCatalogItem::getInstance($dbVendorCatalogItem, $this->getResponseProfile());
		$vendorCatalogItem->fromObject($dbVendorCatalogItem, $this->getResponseProfile());
		return $vendorCatalogItem;
	}
	
	/**
	 * Retrieve specific catalog item by id
	 *
	 * @action get
	 * @param int $id
	 * @return VidiunVendorCatalogItem
	 * @throws VidiunReachErrors::CATALOG_ITEM_NOT_FOUND
	 */
	public function getAction($id)
	{
		$dbVendorCatalogItem = VendorCatalogItemPeer::retrieveByPK($id);
		if(!$dbVendorCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_FOUND, $id);
		
		$vendorCatalogItem = VidiunVendorCatalogItem::getInstance($dbVendorCatalogItem, $this->getResponseProfile());
		$vendorCatalogItem->fromObject($dbVendorCatalogItem, $this->getResponseProfile());
		return $vendorCatalogItem;
	}
	
	/**
	 * List VidiunVendorCatalogItem objects
	 *
	 * @action list
	 * @param VidiunVendorCatalogItemFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunVendorCatalogItemListResponse
	 */
	public function listAction(VidiunVendorCatalogItemFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunVendorCatalogItemFilter();
		
		if(!$pager)
			$pager = new VidiunFilterPager();
		
		return $filter->getTypeListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * Update an existing vedor catalog item object
	 *
	 * @action update
	 * @param int $id
	 * @param VidiunVendorCatalogItem $vendorCatalogItem
	 * @return VidiunVendorCatalogItem
	 *
	 * @throws VidiunReachErrors::CATALOG_ITEM_NOT_FOUND
	 */
	public function updateAction($id, VidiunVendorCatalogItem $vendorCatalogItem)
	{
		// get the object
		$dbVendorCatalogItem = VendorCatalogItemPeer::retrieveByPK($id);
		if(!$dbVendorCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_FOUND, $id);
		
		// save the object
		$dbVendorCatalogItem = $vendorCatalogItem->toUpdatableObject($dbVendorCatalogItem);
		$dbVendorCatalogItem->save();
		
		// return the saved object
		$vendorCatalogItem = VidiunVendorCatalogItem::getInstance($dbVendorCatalogItem, $this->getResponseProfile());
		$vendorCatalogItem->fromObject($dbVendorCatalogItem, $this->getResponseProfile());
		return $vendorCatalogItem;
	}
	
	/**
	 * Update vendor catalog item status by id
	 *
	 * @action updateStatus
	 * @param int $id
	 * @param VidiunVendorCatalogItemStatus $status
	 * @return VidiunVendorCatalogItem
	 *
	 * @throws VidiunReachErrors::CATALOG_ITEM_NOT_FOUND
	 * @throws VidiunReachErrors::VENDOR_CATALOG_ITEM_DUPLICATE_SYSTEM_NAME
	 */
	public function updateStatusAction($id, $status)
	{
		// get the object
		$dbVendorCatalogItem = VendorCatalogItemPeer::retrieveByPK($id);
		if (!$dbVendorCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_FOUND, $id);
		
		if($status == VidiunVendorCatalogItemStatus::ACTIVE)
		{
			//Check uniqueness of new object's system name
			$systemNameTemplates = VendorCatalogItemPeer::retrieveBySystemName($dbVendorCatalogItem->getSystemName(), $id);
			if (count($systemNameTemplates))
				throw new VidiunAPIException(VidiunReachErrors::VENDOR_CATALOG_ITEM_DUPLICATE_SYSTEM_NAME, $dbVendorCatalogItem->getSystemName());
		}
		
		// save the object
		$dbVendorCatalogItem->setStatus($status);
		$dbVendorCatalogItem->save();
		
		// return the saved object
		$vendorCatalogItem = VidiunVendorCatalogItem::getInstance($dbVendorCatalogItem, $this->getResponseProfile());
		$vendorCatalogItem->fromObject($dbVendorCatalogItem, $this->getResponseProfile());
		return $vendorCatalogItem;
	}
	
	/**
	 * Delete vedor catalog item object
	 *
	 * @action delete
	 * @param int $id
	 *
	 * @throws VidiunReachErrors::CATALOG_ITEM_NOT_FOUND
	 */
	public function deleteAction($id)
	{
		// get the object
		$dbVendorCatalogItem = VendorCatalogItemPeer::retrieveByPK($id);
		if (!$dbVendorCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_FOUND, $id);
		
		// Check if partnerCatalogItem exists, in this case you should not be able to delete the vendorCatalogItem prior to deleting the partner assignment first 
		$partnerCatalogItem = PartnerCatalogItemPeer::retrieveByCatalogItemId($id);
		if($partnerCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_CANNOT_BE_DELETED, $id);
		
		// set the object status to deleted
		$dbVendorCatalogItem->setStatus(VidiunVendorCatalogItemStatus::DELETED);
		$dbVendorCatalogItem->save();
	}
}
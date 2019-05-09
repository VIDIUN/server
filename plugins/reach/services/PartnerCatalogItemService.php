<?php
/**
 * Partner Catalog Item Service
 *
 * @service PartnerCatalogItem
 * @package plugins.reach
 * @subpackage api.services
 * @throws VidiunErrors::SERVICE_FORBIDDEN
 */

class PartnerCatalogItemService extends VidiunBaseService
{

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if (!ReachPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, ReachPlugin::PLUGIN_NAME);

		$this->applyPartnerFilterForClass('PartnerCatalogItem');
	}

	/**
	 * Assign existing catalogItem to specific account
	 *
	 * @action add
	 * @param int $id source catalog item to assign to partner
	 * @throws VidiunReachErrors::CATALOG_ITEM_NOT_FOUND
	 * @throws VidiunReachErrors::VENDOR_CATALOG_ITEM_ALREADY_ENABLED_ON_PARTNER
	 *
	 * @return VidiunVendorCatalogItem
	 */
	public function addAction($id)
	{
		// get the object
		$dbVendorCatalogItem = VendorCatalogItemPeer::retrieveByPK($id);
		if (!$dbVendorCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_FOUND, $id);

		//Check if catalog item already enabled on partner
		$dbPartnerCatalogItem = PartnerCatalogItemPeer::retrieveByCatalogItemId($id, vCurrentContext::getCurrentPartnerId());
		if ($dbPartnerCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::VENDOR_CATALOG_ITEM_ALREADY_ENABLED_ON_PARTNER, $id, vCurrentContext::getCurrentPartnerId());

		//Check if catalog item exists but deleted to re-use it
		$partnerCatalogItem = PartnerCatalogItemPeer::retrieveByCatalogItemIdNoFilter($id, vCurrentContext::getCurrentPartnerId());
		if (!$partnerCatalogItem)
		{
			$partnerCatalogItem = new PartnerCatalogItem();
			$partnerCatalogItem->setPartnerId($this->getPartnerId());
			$partnerCatalogItem->setCatalogItemId($id);
		}

		$partnerCatalogItem->setStatus(VidiunVendorCatalogItemStatus::ACTIVE);
		$partnerCatalogItem->save();

		// return the catalog item
		$vendorCatalogItem = VidiunVendorCatalogItem::getInstance($dbVendorCatalogItem, $this->getResponseProfile());
		$vendorCatalogItem->fromObject($dbVendorCatalogItem, $this->getResponseProfile());
		return $vendorCatalogItem;
	}

	/**
	 * Remove existing catalogItem from specific account
	 *
	 * @action delete
	 * @param int $id source catalog item to remove
	 * @throws VidiunReachErrors::CATALOG_ITEM_NOT_FOUND
	 * @throws VidiunReachErrors::VENDOR_CATALOG_ITEM_ALREADY_ENABLED_ON_PARTNER
	 */
	public function deleteAction($id)
	{
		$dbVendorCatalogItem = VendorCatalogItemPeer::retrieveByPK($id);
		if (!$dbVendorCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_FOUND, $id);

		//Check if catalog item already enabled
		$dbPartnerCatalogItem = PartnerCatalogItemPeer::retrieveByCatalogItemId($id, vCurrentContext::getCurrentPartnerId());
		if (!$dbPartnerCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::PARTNER_CATALOG_ITEM_NOT_FOUND, $id);

		$dbPartnerCatalogItem->setStatus(VendorCatalogItemStatus::DELETED);
		$dbPartnerCatalogItem->save();
	}
}
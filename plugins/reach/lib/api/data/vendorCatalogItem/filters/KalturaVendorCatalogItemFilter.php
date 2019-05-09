<?php
/**
 * @package plugins.reach
 * @subpackage api.filters
 */
class VidiunVendorCatalogItemFilter extends VidiunVendorCatalogItemBaseFilter
{
	/**
	 * @var int
	 */
	public $partnerIdEqual;
	
	protected function getCoreFilter()
	{
		return new VendorCatalogItemFilter();
	}
	
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		return $this->doGetListResponse($pager, $responseProfile, $type);
	}
	
	public function doGetListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		$c = new Criteria();
		if($type)
			$c->add(VendorCatalogItemPeer::SERVICE_FEATURE, $type);
		
		$filter = $this->toObject();
		$filter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		
		$partnerIdEqual = null;
		if($this->partnerIdEqual && !in_array(vCurrentContext::$vs_partner_id, array(Partner::ADMIN_CONSOLE_PARTNER_ID, $this->partnerIdEqual)))
		{
			//Add Id that does not exist to break list
			$c->add(VendorCatalogItemPeer::ID, -1);
		}
		elseif ($this->partnerIdEqual && in_array(vCurrentContext::$vs_partner_id, array(Partner::ADMIN_CONSOLE_PARTNER_ID, $this->partnerIdEqual)))
		{
			$partnerIdEqual = $this->partnerIdEqual;
		}
		elseif (!$this->partnerIdEqual && vCurrentContext::$vs_partner_id != Partner::ADMIN_CONSOLE_PARTNER_ID)
		{
			$partnerIdEqual = vCurrentContext::$vs_partner_id;
		}
			
		if($partnerIdEqual)
		{
			$c->add(PartnerCatalogItemPeer::PARTNER_ID, $partnerIdEqual);
			$c->add(PartnerCatalogItemPeer::STATUS, VendorCatalogItemStatus::ACTIVE);
			$c->addJoin(PartnerCatalogItemPeer::CATALOG_ITEM_ID, VendorCatalogItemPeer::ID, Criteria::INNER_JOIN);
		}
		
		$list = VendorCatalogItemPeer::doSelect($c);
		
		$resultCount = count($list);
		if ($resultCount && $resultCount < $pager->pageSize)
			$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
		else
		{
			VidiunFilterPager::detachFromCriteria($c);
			$totalCount = VendorCatalogItemPeer::doCount($c);
		}
		
		$response = new VidiunVendorCatalogItemListResponse();
		$response->objects = VidiunVendorCatalogItemArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/* (non-PHPdoc)
 	 * @see VidiunRelatedFilter::getListResponse()
 	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		return $this->getTypeListResponse($pager, $responseProfile);
	}
}

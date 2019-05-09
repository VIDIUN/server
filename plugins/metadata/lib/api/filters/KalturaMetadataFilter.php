<?php
/**
 * @package plugins.metadata
 * @subpackage api.filters
 */
class VidiunMetadataFilter extends VidiunMetadataBaseFilter
{	
	static private $map_between_objects = array
	(
		"metadataObjectTypeEqual" => "_eq_object_type",
	);

	/* (non-PHPdoc)
	 * @see VidiunMetadataBaseFilter::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * Instantiate default value
	 */
	public function __construct()
	{
		// default value for backward compatibility
		$this->metadataObjectTypeEqual = MetadataObjectType::ENTRY;
	}

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new MetadataFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		if (!$this->metadataObjectTypeEqual)
			throw new VidiunAPIException(MetadataErrors::MUST_FILTER_ON_OBJECT_TYPE);
		
		$objectIds = $this->validateObjectIdFiltered();
		if(!count($objectIds) && $this->metadataObjectTypeEqual != MetadataObjectType::DYNAMIC_OBJECT && $this->shouldBlockEmptyObjectIdsFiltering())
		{
			return $this->getEmptyListResponse();
		}
		
		$this->objectIdEqual = null;
		$this->objectIdIn = implode(',', $objectIds);
		
		$metadataFilter = $this->toObject();

		$c = VidiunCriteria::create(MetadataPeer::OM_CLASS);
		$metadataFilter->attachToCriteria($c);

		$pager->attachToCriteria($c);
		$list = MetadataPeer::doSelect($c);
		
		$response = new VidiunMetadataListResponse();
		$response->objects = VidiunMetadataArray::fromDbArray($list, $responseProfile);
		
		if($c instanceof SphinxMetadataCriteria)
		{
			$response->totalCount = $c->getRecordsCount();
		}
		elseif($pager->pageIndex == 1 && count($response->objects) < $pager->pageSize)
		{
			$response->totalCount = count($response->objects);
		}
		else
		{
			$pager->detachFromCriteria($c);
			$response->totalCount = MetadataPeer::doCount($c);
		}
		
		return $response;
	}
	
	private function validateObjectIdFiltered()
	{
		$objectIds = $this->getObjectIdsFiltered();
		
		if(($this->metadataObjectTypeEqual == MetadataObjectType::ENTRY || vEntitlementUtils::getEntitlementEnforcement()) && 
			empty($objectIds) && $this->shouldBlockEmptyObjectIdsFiltering())
			throw new VidiunAPIException(MetadataErrors::MUST_FILTER_ON_OBJECT_ID);
		
		if ($this->metadataObjectTypeEqual == MetadataObjectType::ENTRY)
		{
			$objectIds = array_map('strtolower', $objectIds);
			$objectIds = !empty($objectIds) ? entryPeer::filterEntriesByPartnerOrVidiunNetwork($objectIds, vCurrentContext::getCurrentPartnerId()) : array();
		}
		elseif($this->metadataObjectTypeEqual == VidiunMetadataObjectType::USER)
		{
			$vusers = !empty($objectIds) ? vuserPeer::getVuserByPartnerAndUids(vCurrentContext::getCurrentPartnerId(), $objectIds) : array();
			$objectIds = array();
			foreach($vusers as $vuser)
				$objectIds[] = $vuser->getId();
		}
		elseif($this->metadataObjectTypeEqual == MetadataObjectType::CATEGORY)
		{
			$categories = !empty($objectIds) ? categoryPeer::retrieveByPKs($objectIds) : array();
			$objectIds = array();
			foreach($categories as $category)
					$objectIds[] = $category->getId();
		}
		
		return $objectIds;
	}
	
	private function shouldBlockEmptyObjectIdsFiltering()
	{
		if(vCurrentContext::$vs_partner_id == Partner::BATCH_PARTNER_ID)
			return false;
		
		$metadataListNoFilterExcludePartners = vConf::get('metadata_list_without_object_filtering_partners', 'local', array());
		if(!array_key_exists(vCurrentContext::getCurrentPartnerId(), $metadataListNoFilterExcludePartners))
			return true;
		
		$allowedFilterTypes = $metadataListNoFilterExcludePartners[vCurrentContext::getCurrentPartnerId()];
		if($allowedFilterTypes == "")
			return false;
		
		$allowedFilterTypesArray = explode(",", $allowedFilterTypes);
		if(!in_array($this->metadataObjectTypeEqual, $allowedFilterTypesArray))
			return true;
		
		return false;
	}
	
	public function getObjectIdsFiltered()
	{
		$objectIds = array();
		if ($this->objectIdEqual)
		{
			$objectIds = array($this->objectIdEqual);
		}
		else if ($this->objectIdIn)
		{
			$objectIds = explode(',', $this->objectIdIn);
		}
		
		return $objectIds;
	}
	
	public function getEmptyListResponse()
	{
		$response = new VidiunMetadataListResponse();
		$response->objects = new VidiunMetadataArray();
		$response->totalCount = 0;
		return $response;
	}
}

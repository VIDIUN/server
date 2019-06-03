<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunAssetFilter extends VidiunAssetBaseFilter
{
	/**
	 * @dynamicType VidiunAssetType
	 * @var string
	 */
	public $typeIn;
	
	static private $map_between_objects = array
	(
		"typeIn" => "_in_type",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	protected function validateEntryIdFiltered()
	{
		if(!$this->entryIdEqual && !$this->entryIdIn)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, $this->getFormattedPropertyNameWithClassName('entryIdEqual') . '/' . $this->getFormattedPropertyNameWithClassName('entryIdIn'));
	}

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new AssetFilter();
	}
	
	protected function doGetListResponse(VidiunFilterPager $pager, array $types = null)
	{
		$this->validateEntryIdFiltered();
		
	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;
	    
		// verify access to the relevant entries - either same partner as the VS or vidiun network
		if ($this->entryIdEqual)
		{
			$entryIds = array($this->entryIdEqual);
		}
		else if ($this->entryIdIn)
		{
			$entryIds = explode(',', $this->entryIdIn);
		}
		else
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, 'VidiunAssetFilter::entryIdEqual/VidiunAssetFilter::entryIdIn');
		}
		
		$entryIds = entryPeer::filterEntriesByPartnerOrVidiunNetwork($entryIds, vCurrentContext::getCurrentPartnerId());
		if (!$entryIds)
		{
			return array(array(), 0);
		}
		
		$this->entryIdEqual = null;
		$this->entryIdIn = implode(',', $entryIds);

		// get the flavors
		$flavorAssetFilter = new AssetFilter();
		
		$this->toObject($flavorAssetFilter);

		$c = new Criteria();
		$flavorAssetFilter->attachToCriteria($c);
		
		if ($flavorAssetFilter->get('_in_type'))
        {
        	//If the $types array is empty we should not return results on the query.
        	$types = array_intersect($types, explode (',', $flavorAssetFilter->get('_in_type')));
        	if(!count($types))
        	{
        		myDbHelper::$use_alternative_con = null;
                return array(array(), 0);
        	}
        }
        
		if($types)
		{
			$c->add(assetPeer::TYPE, $types, Criteria::IN);
		}

		$pager->attachToCriteria($c);
		$list = assetPeer::doSelect($c);

		$resultCount = count($list);
		if ($resultCount && $resultCount < $pager->pageSize)
			$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
		else
		{
			VidiunFilterPager::detachFromCriteria($c);
			$totalCount = assetPeer::doCount($c);
		}
		
		myDbHelper::$use_alternative_con = null;
		
		return array($list, $totalCount);
	}

	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, array $types = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager, $types);
		
		$response = new VidiunFlavorAssetListResponse();
		$response->objects = VidiunFlavorAssetArray::fromDbArray($list, $responseProfile);
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

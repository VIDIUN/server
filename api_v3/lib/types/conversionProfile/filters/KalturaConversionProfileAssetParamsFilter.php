<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunConversionProfileAssetParamsFilter extends VidiunConversionProfileAssetParamsBaseFilter
{
	/**
	 * @var VidiunConversionProfileFilter
	 */
	public $conversionProfileIdFilter;
	
	/**
	 * @var VidiunAssetParamsFilter
	 */
	public $assetParamsIdFilter;

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new assetParamsConversionProfileFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::toObject()
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		$conversionProfileCriteria = new Criteria();
		
		if($this->conversionProfileIdEqual)
			$conversionProfileCriteria->add(conversionProfile2Peer::ID, $this->conversionProfileIdEqual);
		if($this->conversionProfileIdIn)
			$conversionProfileCriteria->add(conversionProfile2Peer::ID, explode(',', $this->conversionProfileIdIn), Criteria::IN);
		if($this->conversionProfileIdFilter)
		{
			$conversionProfileIdFilter = new conversionProfile2Filter();
			$this->conversionProfileIdFilter->toObject($conversionProfileIdFilter);
			$conversionProfileIdFilter->attachToCriteria($conversionProfileCriteria);
		}
		$this->conversionProfileIdEqual = null;
		$this->conversionProfileIdFilter = null;
		$conversionProfileIdIn = conversionProfile2Peer::getIds($conversionProfileCriteria);
		if(count($conversionProfileIdIn))
			$this->conversionProfileIdIn = implode(',', $conversionProfileIdIn);
		else
			$this->conversionProfileIdIn = -1; // none existing conversion profile
		
		
		$assetParamsCriteria = new Criteria();
		
		if($this->assetParamsIdEqual)
			$assetParamsCriteria->add(assetParamsPeer::ID, $this->assetParamsIdEqual);
		if($this->assetParamsIdIn)
			$assetParamsCriteria->add(assetParamsPeer::ID, explode(',', $this->assetParamsIdIn), Criteria::IN);
		if($this->assetParamsIdFilter)
		{
			$assetParamsIdFilter = new assetParamsFilter();
			$this->assetParamsIdFilter->toObject($assetParamsIdFilter);
			$assetParamsIdFilter->attachToCriteria($assetParamsCriteria);
		}
		$this->assetParamsIdEqual = null;
		$this->assetParamsIdFilter = null;
		$assetParamsIdIn = assetParamsPeer::getIds($assetParamsCriteria);
		if(count($assetParamsIdIn))
			$this->assetParamsIdIn = implode(',', $assetParamsIdIn);
		else
			$this->assetParamsIdIn = -1; // none existing flavor
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$assetParamsConversionProfileFilter = $this->toObject();

		$c = new Criteria();
		$assetParamsConversionProfileFilter->attachToCriteria($c);
		
		$totalCount = flavorParamsConversionProfilePeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = flavorParamsConversionProfilePeer::doSelect($c);
		
		$list = VidiunConversionProfileAssetParamsArray::fromDbArray($dbList, $responseProfile);
		$response = new VidiunConversionProfileAssetParamsListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response; 
	}
}

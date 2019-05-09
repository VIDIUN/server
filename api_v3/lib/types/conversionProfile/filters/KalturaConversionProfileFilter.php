<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunConversionProfileFilter extends VidiunConversionProfileBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new conversionProfile2Filter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$conversionProfile2Filter = $this->toObject();

		$c = new Criteria();
		$conversionProfile2Filter->attachToCriteria($c);
		
		$totalCount = conversionProfile2Peer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = conversionProfile2Peer::doSelect($c);
		
		$list = VidiunConversionProfileArray::fromDbArray($dbList, $responseProfile);
		$list->loadFlavorParamsIds();
		$response = new VidiunConversionProfileListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;  
	}
}

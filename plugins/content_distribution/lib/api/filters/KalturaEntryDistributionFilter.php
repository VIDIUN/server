<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.filters
 */
class VidiunEntryDistributionFilter extends VidiunEntryDistributionBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new EntryDistributionFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$c = new Criteria();
		$entryDistributionFilter = $this->toObject();
		
		$entryDistributionFilter->attachToCriteria($c);
		$count = EntryDistributionPeer::doCount($c);
		
		$pager->attachToCriteria ( $c );
		$list = EntryDistributionPeer::doSelect($c);
		
		$response = new VidiunEntryDistributionListResponse();
		$response->objects = VidiunEntryDistributionArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $count;
	
		return $response;
	}
}

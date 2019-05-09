<?php
/**
 * Partner Aggregation service
 *
 * @service partnerAggregation
 * @package plugins.partnerAggregation
 * @subpackage api.services
 */
class PartnerAggregationService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		$this->applyPartnerFilterForClass('DwhHourlyPartner');
	}
	
	/**
	 * List aggregated partner data
	 * 
	 * @action list
	 * @param VidiunDwhHourlyPartnerFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunDwhHourlyPartnerListResponse
	 */
	function listAction(VidiunDwhHourlyPartnerFilter $filter, VidiunFilterPager $pager = null)
	{
		$filter->validatePropertyNotNull('aggregatedTimeLessThanOrEqual');
		$filter->validatePropertyNotNull('aggregatedTimeGreaterThanOrEqual');

		if (!$pager)
			$pager = new VidiunFilterPager();
		
		$c = new Criteria();			
		$dwhHourlyPartnerFilter = $filter->toObject();
		$dwhHourlyPartnerFilter->attachToCriteria($c);
		$count = DwhHourlyPartnerPeer::doCount($c);
		$pager->attachToCriteria($c);
		$list = DwhHourlyPartnerPeer::doSelect($c);
		
		$response = new VidiunDwhHourlyPartnerListResponse();
		$response->objects = VidiunDwhHourlyPartnerArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
	
		return $response;
	}	
}

<?php
/**
 * @package plugins.reach
 * @subpackage api.filters
 */
class VidiunEntryVendorTaskFilter extends VidiunEntryVendorTaskBaseFilter
{
	/**
	 * @var string
	 */
	public $freeText;
	
	static private $map_between_objects = array
	(
		"userIdEqual" => "_eq_vuser_id",
		"freeText" => "_free_text",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	protected function getCoreFilter()
	{
		return new EntryVendorTaskFilter();
	}
	
	
	/* (non-PHPdoc)
 	 * @see VidiunRelatedFilter::getListResponse()
 	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$c = VidiunCriteria::create(EntryVendorTaskPeer::OM_CLASS);
		$filter = $this->toObject();
		$filter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		
		$this->fixFilterUserId($c);
		
		$list = EntryVendorTaskPeer::doSelect($c);
		$totalCount = $c->getRecordsCount();
		
		$response = new VidiunEntryVendorTaskListResponse();
		$response->objects = VidiunEntryVendorTaskArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/**
	 * The user_id is infact a puser_id and the vuser_id should be retrieved
	 */
	private function fixFilterUserId(Criteria $c)
	{
		if ($this->userIdEqual !== null) 
		{
			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::getCurrentPartnerId(), $this->userIdEqual);
			if ($vuser)
				$c->add(EntryVendorTaskPeer::VUSER_ID, $vuser->getId());
			else
				$c->add(EntryVendorTaskPeer::VUSER_ID, -1); // no result will be returned when the user is missing
		}
	}
}

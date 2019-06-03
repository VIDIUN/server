<?php
/**
 * @package plugins.audit
 * @subpackage api.filters
 */
class VidiunAuditTrailFilter extends VidiunAuditTrailBaseFilter
{
	static private $map_between_objects = array
	(
		"auditObjectTypeEqual" => "_eq_object_type",
		"auditObjectTypeIn" => "_in_object_type",
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), self::$order_by_map);
	}

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new AuditTrailFilter();
	}
	
	/**
	 * @param AuditTrailFilter $auditTrailFilter
	 * @param array $propsToSkip
	 * @return AuditTrailFilter
	 */
	public function toObject($auditTrailFilter = null, $propsToSkip = array())
	{
		if(isset($this->userIdEqual))
		{
			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::$vs_partner_id, $this->userIdEqual, true);
			if($vuser)
				$this->userIdEqual = $vuser->getId();
		}
		
		if(isset($this->userIdIn))
		{
			$vusers = vuserPeer::getVuserByPartnerAndUids(vCurrentContext::$vs_partner_id, $this->userIdIn);
			$vuserIds = array();
			foreach($vusers as $vuser)
				$vuserIds[] = $vuser->getId();
				
			$this->userIdIn = implode(',', $vuserIds);
		}
			
		return parent::toObject($auditTrailFilter, $propsToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$auditTrailFilter = $this->toObject();
		
		$c = new Criteria();
		$auditTrailFilter->attachToCriteria($c);
		$count = AuditTrailPeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$list = AuditTrailPeer::doSelect($c);
		
		$response = new VidiunAuditTrailListResponse();
		$response->objects = VidiunAuditTrailArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $count;
		
		return $response;
	}
}

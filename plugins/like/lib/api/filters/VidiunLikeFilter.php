<?php
/**
 * @package plugins.like
 * @subpackage api.filters
 */
class VidiunLikeFilter extends VidiunLikeBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	public function getCoreFilter()
	{
		return new LikeFilter();
	} 
	
	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;
	
		$c = new Criteria();
		$c->add(vvotePeer::PARTNER_ID, vCurrentContext::$vs_partner_id);
	
		if($this->entryIdEqual)
				$c->add(vvotePeer::ENTRY_ID, $this->entryIdEqual);
		if($this->userIdEqual)
		{
			$vuser = vuserPeer::getActiveVuserByPartnerAndUid(vCurrentContext::$vs_partner_id, $this->userIdEqual);
			if(!$vuser)
				throw new VidiunAPIException(VidiunErrors::USER_NOT_FOUND);
			$c->add(vvotePeer::VUSER_ID, $vuser->getId());
		}
		if($this->createdAtGreaterThanOrEqual)
			$c->add(vvotePeer::CREATED_AT,$this->createdAtGreaterThanOrEqual, Criteria::GREATER_EQUAL);
		if($this->createdAtLessThanOrEqual)
			$c->addAnd(vvotePeer::CREATED_AT,$this->createdAtLessThanOrEqual, Criteria::LESS_EQUAL);
		if($this->entryIdIn)
			$c->add(vvotePeer::ENTRY_ID,explode(',',$this->entryIdIn),Criteria::IN);

		$pager->attachToCriteria($c);
	
		$list = vvotePeer::doSelect($c);
	
		$response = new VidiunLikeListResponse();
		$response->objects = VidiunLikeArray::fromDbArray($list, $responseProfile);
		$response->totalCount = count($list);
		return $response;
	}
	
}

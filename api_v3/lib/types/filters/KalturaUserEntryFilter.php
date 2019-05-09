<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunUserEntryFilter extends VidiunUserEntryBaseFilter
{

	/**
	 * @var VidiunNullableBoolean
	 */
	public $userIdEqualCurrent;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $isAnonymous;
	
	/**
	 * @var string
	 */
	public $privacyContextEqual;
	
	/**
	 * @var string
	 */
	public $privacyContextIn;

	/**
	 * @var int
	 */
	public $partnerId;

	static private $map_between_objects = array
	(
		"privacyContextEqual" => "_eq_privacy_context",
		"privacyContextIn" => "_in_privacy_context",
		"partnerId" => "_eq_partner_id"
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * @return baseObjectFilter
	 */
	protected function getCoreFilter()
	{
		return new UserEntryFilter();
	}
	
	protected function validateFilter()
	{
		if(!$this->userIdEqual && !$this->userIdIn && !$this->entryIdEqual && !$this->entryIdIn)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL,
				$this->getFormattedPropertyNameWithClassName('userIdEqual') . '/' . $this->getFormattedPropertyNameWithClassName('userIdIn') . '/' .
				$this->getFormattedPropertyNameWithClassName('entryIdEqual') . '/' . $this->getFormattedPropertyNameWithClassName('entryIdIn'));
	}
	
	/**
	 * @param VidiunFilterPager $pager
	 * @param VidiunDetachedResponseProfile $responseProfile
	 * @return VidiunListResponse
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$response = new VidiunUserEntryListResponse();
		if ( in_array(vCurrentContext::getCurrentSessionType(), array(vSessionBase::SESSION_TYPE_NONE,vSessionBase::SESSION_TYPE_WIDGET)) )
		{
			$response->totalCount = 0;
			return $response;
		}

		$c = new Criteria();
		
		$userEntryFilter = $this->toObject();
		$userEntryFilter->attachToCriteria($c);

		$pager->attachToCriteria($c);
		
		$list = UserEntryPeer::doSelect($c);
		$resultCount = count($list);
		if ($resultCount && ($resultCount < $pager->pageSize))
		{
			$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
		}
		else
		{
			VidiunFilterPager::detachFromCriteria($c);
			$totalCount = UserEntryPeer::doCount($c);
		}

		$response->totalCount = $totalCount;
		$response->objects = VidiunUserEntryArray::fromDbArray($list, $responseProfile);
		return $response;
	}
	

	public function toObject ($object_to_fill = null, $props_to_skip = array())
	{
		if (vCurrentContext::$vs_partner_id != Partner::BATCH_PARTNER_ID)
		{
			if (!is_null($this->privacyContextEqual) || !is_null($this->privacyContextIn))
			{
				throw new VidiunAPIException(VidiunErrors::USER_ENTRY_FILTER_FORBIDDEN_FIELDS_USED);
			}
			$this->partnerId = vCurrentContext::getCurrentPartnerId();
		}
		
		if (!is_null($this->userIdEqualCurrent) && $this->userIdEqualCurrent)
		{
			$this->userIdEqual = vCurrentContext::getCurrentVsVuserId();
		}
		else
		{
			$this->fixFilterUserId();
		}
		$this->validateFilter();
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
	
	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $srcObj UserEntryFilter */
		parent::doFromObject($srcObj, $responseProfile);
		if (vCurrentContext::$vs_partner_id == Partner::BATCH_PARTNER_ID) //batch should be able to get userEntry objects of deleted users.
				vuserPeer::setUseCriteriaFilter(false);
		
		if ($srcObj->get('_eq_user_id'))
		{
			$this->userIdEqual = $this->prepareVusersToPusersFilter($srcObj->get('_eq_user_id'));
		}
		if ($srcObj->get('_in_user_id'))
		{
			$this->userIdIn = $this->prepareVusersToPusersFilter($srcObj->get('_in_user_id'));
		}
		if ($srcObj->get('_notin_user_id'))
		{
			$this->userIdNotIn = $this->prepareVusersToPusersFilter($srcObj->get('_notin_user_id'));
		}
		
	}


	/**
	 * The user_id is infact a puser_id and the vuser_id should be retrieved
	 */
	protected function fixFilterUserId()
	{
		if (vCurrentContext::$vs_partner_id == Partner::BATCH_PARTNER_ID)
		{
			vCurrentContext::$partner_id = $this->partnerId;
		}

		if ($this->userIdEqual !== null)
		{
			if (vCurrentContext::$vs_partner_id == Partner::BATCH_PARTNER_ID) //batch should be able to get userEntry objects of deleted users.
				vuserPeer::setUseCriteriaFilter(false);

			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::getCurrentPartnerId(), $this->userIdEqual);
			vuserPeer::setUseCriteriaFilter(true);
			if ($vuser)
				$this->userIdEqual = $vuser->getId();
			else
				$this->userIdEqual = -1; // no result will be returned when the user is missing
		}

		if(!empty($this->userIdIn))
		{
			$this->userIdIn = $this->preparePusersToVusersFilter( $this->userIdIn );
		}
		if(!empty($this->userIdNotIn))
		{
			$this->userIdNotIn = $this->preparePusersToVusersFilter( $this->userIdNotIn );
		}

		if(!is_null($this->isAnonymous))
		{
			if(VidiunNullableBoolean::toBoolean($this->isAnonymous)===false)
				$this->userIdNotIn .= self::getListOfAnonymousUsers();

			elseif(VidiunNullableBoolean::toBoolean($this->isAnonymous)===true)
				$this->userIdIn .= self::getListOfAnonymousUsers();
		}
	}

	public static function getListOfAnonymousUsers()
	{
		$anonVuserIds = "";
		$anonVusers = vuserPeer::getVuserByPartnerAndUids(vCurrentContext::getCurrentPartnerId(), array(0,''));
		foreach ($anonVusers as $anonVuser) {
			$anonVuserIds .= ",".$anonVuser->getVuserId();
		}
		return $anonVuserIds;
	}
	
	public function getEmptyListResponse()
	{
		$res = new VidiunUserEntryListResponse();
		$res->objects = array();
		$res->totalCount = 0;
		return $res;
	}
}

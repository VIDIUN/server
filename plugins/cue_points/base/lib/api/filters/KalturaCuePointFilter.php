<?php
/**
 * @package plugins.cuePoint
 * @subpackage api.filters
 */
class VidiunCuePointFilter extends VidiunCuePointBaseFilter
{
	/**
	 * @var string
	 */
	public $freeText;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $userIdEqualCurrent;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $userIdCurrent;
	
	static private $map_between_objects = array
	(
		"cuePointTypeEqual" => "_eq_type",
		"cuePointTypeIn" => "_in_type",
		"freeText" => "_free_text",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	protected function validateEntryIdFiltered()
	{
		if(!$this->idEqual && !$this->idIn && !$this->entryIdEqual && !$this->entryIdIn)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL,
					$this->getFormattedPropertyNameWithClassName('idEqual') . '/' . $this->getFormattedPropertyNameWithClassName('idIn') . '/' .
					$this->getFormattedPropertyNameWithClassName('entryIdEqual') . '/' . $this->getFormattedPropertyNameWithClassName('entryIdIn'));
	}

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new CuePointFilter();
	}
	
	protected function translateUserIds()
	{		
		if($this->userIdCurrent == VidiunNullableBoolean::TRUE_VALUE)
		{
			if(vCurrentContext::$vs_vuser_id)
			{
				$this->userIdEqual = vCurrentContext::$vs_vuser_id;
			}
			else
			{
				$this->isPublicEqual = VidiunNullableBoolean::TRUE_VALUE;
			}
			$this->userIdCurrent = null;
		}
		
		if(isset($this->userIdEqual)){
			$dbVuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::$vs_partner_id, $this->userIdEqual);
			if (! $dbVuser) {
				throw new VidiunAPIException ( VidiunErrors::INVALID_USER_ID );
			}
			$this->userIdEqual = $dbVuser->getId();
		}
		
		if(isset($this->userIdIn)){
			$userIds = explode(",", $this->userIdIn);
			foreach ($userIds as $userId){
				$dbVuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::$vs_partner_id, $userId);
				if (! $dbVuser) {
				    throw new VidiunAPIException ( VidiunErrors::INVALID_USER_ID );
			}
				$vuserIds = $dbVuser->getId().",";
			}
			
			$this->userIdIn = $vuserIds;
		}
	}
	
	protected function getCriteria()
	{
	    return VidiunCriteria::create(CuePointPeer::OM_CLASS);
	}
	
	protected function doGetListResponse(VidiunFilterPager $pager, $type = null)
	{
		$this->validateEntryIdFiltered();
		
		if (!is_null($this->userIdEqualCurrent) && $this->userIdEqualCurrent)
		{
			$this->userIdEqual = vCurrentContext::getCurrentVsVuserId();
		}
		else
		{
			$this->translateUserIds();
		}
		
		$c = $this->getCriteria();

		if($type)
		{
			$this->cuePointTypeEqual = $type;
			$this->cuePointTypeIn = null;
		}

		$entryIds = $this->getFilteredEntryIds();
		if (!is_null($entryIds))
		{
			$entryIds = entryPeer::filterEntriesByPartnerOrVidiunNetwork ( $entryIds, vCurrentContext::getCurrentPartnerId());
			if (!$entryIds)
			{
				return array(array(), 0);
			}
			
			$this->entryIdEqual = null;
			$this->entryIdIn = implode ( ',', $entryIds );
			$this->applyPartnerOnCurrentContext($entryIds);
		}

		$cuePointFilter = $this->toObject();
		$cuePointFilter->attachToCriteria($c);

		$pager->attachToCriteria($c);
			
		$list = CuePointPeer::doSelect($c);
		
		return array($list, $c->getRecordsCount());
	}
	
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		//Was added to avoid braking backward compatibility for old player chapters module
		if(isset($this->tagsLike) && $this->tagsLike==VidiunAnnotationFilter::CHAPTERS_PUBLIC_TAG)
			VidiunCriterion::disableTag(VidiunCriterion::TAG_WIDGET_SESSION);

		list($list, $totalCount) = $this->doGetListResponse($pager, $type);
		$response = new VidiunCuePointListResponse();
		$response->objects = VidiunCuePointArray::fromDbArray($list, $responseProfile);
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

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::validateForResponseProfile()
	 */
	public function validateForResponseProfile()
	{
		if(		!vCurrentContext::$is_admin_session
			&&	!$this->idEqual
			&&	!$this->idIn
			&&	!$this->systemNameEqual
			&&	!$this->systemNameIn)
		{
			if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENABLE_RESPONSE_PROFILE_USER_CACHE, vCurrentContext::getCurrentPartnerId()))
			{
				VidiunResponseProfileCacher::useUserCache();
				return;
			}
			
			throw new VidiunAPIException(VidiunCuePointErrors::USER_VS_CANNOT_LIST_RELATED_CUE_POINTS, get_class($this));
		}
	}
	
	public function applyPartnerOnCurrentContext($entryIds)
	{
		if(vCurrentContext::getCurrentPartnerId() >= 0 || !$entryIds)
			return;
		
		$entryId = reset($entryIds);
		$entry = entryPeer::retrieveByPKNoFilter($entryId);
		if($entry)
		{
			vCurrentContext::$partner_id = $entry->getPartnerId();
		}
		else
		{
			VidiunLog::debug("Entry id not filtered, If partner id not correctly defined wrong results set may be returned");
		}
	}
	
	public function getFilteredEntryIds()
	{
		$entryIds = null;
		
		if ($this->entryIdEqual)
		{
			$entryIds = array($this->entryIdEqual);
		}
		elseif ($this->entryIdIn)
		{
			$entryIds = explode(',', $this->entryIdIn);
		}
		
		return $entryIds;
	}
}

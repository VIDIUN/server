<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunGroupUserFilter extends VidiunGroupUserBaseFilter
{

	static private $map_between_objects = array	();

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new VuserVgroupFilter();
	}
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}


	protected function validateUserIdOrGroupIdFiltered()
	{
		if(!$this->userIdEqual && !$this->userIdIn && !$this->groupIdEqual && !$this->groupIdIn)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, $this->getFormattedPropertyNameWithClassName('userIdEqual') . '/' . $this->getFormattedPropertyNameWithClassName('userIdIn') . '/' . $this->getFormattedPropertyNameWithClassName('groupIdEqual') . '/' . $this->getFormattedPropertyNameWithClassName('groupIdIn'));
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$this->validateUserIdOrGroupIdFiltered();
		
		if($this->groupIdEqual)
		{
			$partnerId = vCurrentContext::getCurrentPartnerId();

			$c = new Criteria();
			$c->add(vuserPeer::PARTNER_ID, $partnerId);
			$c->add(vuserPeer::PUSER_ID, $this->groupIdEqual);
			$c->add(vuserPeer::TYPE, VuserType::GROUP);
			if (vCurrentContext::$vs_partner_id == Partner::BATCH_PARTNER_ID) //batch should be able to get categoryUser of deleted users.
				vuserPeer::setUseCriteriaFilter(false);

			// in case of more than one deleted vusers - get the last one
			$c->addDescendingOrderByColumn(vuserPeer::UPDATED_AT);

			$vuser = vuserPeer::doSelectOne($c);
			vuserPeer::setUseCriteriaFilter(true);

			if (!$vuser)
			{
				$response = new VidiunGroupUserListResponse();
				$response->objects = new VidiunGroupUserArray();
				$response->totalCount = 0;

				return $response;
			}

			$this->groupIdEqual = $vuser->getId();
		}

		if($this->userIdEqual)
		{
			$partnerId = vCurrentContext::getCurrentPartnerId();

			$c = new Criteria();
			$c->add(vuserPeer::PARTNER_ID, $partnerId);
			$c->add(vuserPeer::PUSER_ID, $this->userIdEqual);
			$c->add(vuserPeer::TYPE, VuserType::USER);
			$vuser = vuserPeer::doSelectOne($c);

			if (!$vuser)
			{
				$response = new VidiunGroupUserListResponse();
				$response->objects = new VidiunGroupUserArray();
				$response->totalCount = 0;

				return $response;
			}

			$this->userIdEqual = $vuser->getId();
		}

		if($this->userIdIn)
		{
			$usersIds = explode(',', $this->userIdIn);
			$partnerId = vCurrentContext::getCurrentPartnerId();

			$c = new Criteria();
			$c->add(vuserPeer::PARTNER_ID, $partnerId, Criteria::EQUAL);
			$c->add(vuserPeer::PUSER_ID, $usersIds, Criteria::IN);
			$c->add(vuserPeer::TYPE, VuserType::USER);
			$vusers = vuserPeer::doSelect($c);

			if (!$vusers)
			{
				$response = new VidiunGroupUserListResponse();
				$response->objects = new VidiunGroupUserArray();
				$response->totalCount = 0;

				return $response;
			}

			$usersIds = array();
			foreach($vusers as $vuser)
			{
				/* @var $vuser vuser */
				$usersIds[] = $vuser->getId();
			}

			$this->userIdIn = implode(',', $usersIds);
		}

		if($this->groupIdIn)
		{
			$groupIdIn = explode(',', $this->groupIdIn);
			$partnerId = vCurrentContext::getCurrentPartnerId();

			$c = new Criteria();
			$c->add(vuserPeer::PARTNER_ID, $partnerId, Criteria::EQUAL);
			$c->add(vuserPeer::PUSER_ID, $groupIdIn, Criteria::IN);
			$c->add(vuserPeer::TYPE, VuserType::GROUP);
			$vusers = vuserPeer::doSelect($c);

			if (!$vusers)
			{
				$response = new VidiunGroupUserListResponse();
				$response->objects = new VidiunGroupUserArray();
				$response->totalCount = 0;

				return $response;
			}

			$groupIdIn = array();
			foreach($vusers as $vuser)
			{
				/* @var $vuser vuser */
				$groupIdIn[] = $vuser->getId();
			}

			$this->groupIdIn = implode(',', $groupIdIn);
		}

		$vuserVgroupFilter = $this->toObject();
		
		$c = VidiunCriteria::create(VuserVgroupPeer::OM_CLASS);
		$vuserVgroupFilter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		$c->applyFilters();
		
		$list = VuserVgroupPeer::doSelect($c);

		$newList = VidiunGroupUserArray::fromDbArray($list, $responseProfile);
		
		$response = new VidiunGroupUserListResponse();
		$response->objects = $newList;
		$resultCount = count($newList);
		if ($resultCount && $resultCount < $pager->pageSize)
			$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
		else
		{
			VidiunFilterPager::detachFromCriteria($c);
			$totalCount = VuserVgroupPeer::doCount($c);
		}
		$response->totalCount = $totalCount;
		return $response;
	}
}

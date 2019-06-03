<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunCategoryUserFilter extends VidiunCategoryUserBaseFilter
{
	static private $map_between_objects = array
	(
		"freeText" => "_mlikeor_screen_name-puser_id",
		"categoryDirectMembers" => "_category_direct_members",
	);

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new categoryVuserFilter();
	}
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	
	/**
	 * Return the list of categoryUser that are not inherited from parent category - only the direct categoryUsers.
	 * @var bool
	 * @requiresPermission read
	 */
	public $categoryDirectMembers;
	
	/**
	 * Free text search on user id or screen name
	 * @var string
	 */
	public $freeText;

	/**
	 * Return a list of categoryUser that related to the userId in this field by groups
	 * @var string
	 */
	public $relatedGroupsByUserId;

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		if($this->userIdIn)
		{
			$usersIds = explode(',', $this->userIdIn);
			$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;

			$c = new Criteria();
			$c->add(vuserPeer::PARTNER_ID, $partnerId, Criteria::EQUAL);
			$c->add(vuserPeer::PUSER_ID, $usersIds, Criteria::IN);
			$vusers = vuserPeer::doSelect($c);
			
			$usersIds = array();
			foreach($vusers as $vuser)
			{
				/* @var $vuser vuser */
				$usersIds[] = $vuser->getId();
			}
				
			$this->userIdIn = implode(',', $usersIds);
		}

		if ($this->relatedGroupsByUserId){
			$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
			$userIds = array();
			$c = new Criteria();
			$c->add(vuserPeer::PARTNER_ID, $partnerId);
			$c->add(vuserPeer::PUSER_ID, $this->relatedGroupsByUserId);
			$c->add(vuserPeer::TYPE, VuserType::USER);
			$vuser = vuserPeer::doSelectOne($c);
			if (!$vuser){
				$response = new VidiunCategoryUserListResponse();
				$response->objects = new VidiunCategoryUserArray();
				$response->totalCount = 0;
				return $response;
			}

			$vgroupIds = VuserVgroupPeer::retrieveVgroupIdsByVuserId($vuser->getId());
			if (!is_null($vgroupIds) && is_array($vgroupIds))
				$userIds = $vgroupIds;
			$userIds[] = $vuser->getId();

			// if userIdIn is also set in the filter need to intersect the two arrays.
			if(isset($this->userIdIn)){
				$curUserIds = explode(',',$this->userIdIn);
				$userIds = array_intersect($curUserIds, $userIds);
			}

			$this->userIdIn = implode(',', $userIds);
		}
		
		if($this->userIdEqual)
		{
			$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
			
			$c = new Criteria();
			$c->add(vuserPeer::PARTNER_ID, $partnerId);
			$c->add(vuserPeer::PUSER_ID, $this->userIdEqual);
			
			if (vCurrentContext::$vs_partner_id == Partner::BATCH_PARTNER_ID) //batch should be able to get categoryUser of deleted users.
				vuserPeer::setUseCriteriaFilter(false);

			// in case of more than one deleted vusers - get the last one
			$c->addDescendingOrderByColumn(vuserPeer::UPDATED_AT);

			$vuser = vuserPeer::doSelectOne($c);
			vuserPeer::setUseCriteriaFilter(true);
			
			if (!$vuser)
			{
				$response = new VidiunCategoryUserListResponse();
        		$response->objects = new VidiunCategoryUserArray();
        		$response->totalCount = 0;
        		
        		return $response;
			}
				
			$this->userIdEqual = $vuser->getId();
		}	

		$categories = array();
		if ($this->categoryIdEqual)
		{
			$categories[] = categoryPeer::retrieveByPK($this->categoryIdEqual);
		}
		elseif($this->categoryIdIn)
		{
			$categories = categoryPeer::retrieveByPKs(explode(',', $this->categoryIdIn));
		}
		
		$categoriesInheritanceRoot = array();
		foreach ($categories as $category)
		{
			/* @var $category category */
			if(is_null($category))
				continue;
				
			if($category->getInheritanceType() == InheritanceType::INHERIT)
			{
				if($this->categoryDirectMembers && vCurrentContext::$master_partner_id == Partner::BATCH_PARTNER_ID)
				{
					$categoriesInheritanceRoot[$category->getId()] = $category->getId();
				}
				else
				{
					//if category inheris members - change filter to -> inherited from parent id = category->getIheritedParent
					$categoriesInheritanceRoot[$category->getInheritedParentId()] = $category->getInheritedParentId();	
				}
			}
			else
			{
				$categoriesInheritanceRoot[$category->getId()] = $category->getId();
			}
		}
		$this->categoryDirectMembers = null;
		$this->categoryIdEqual = null;
		$this->categoryIdIn = implode(',', $categoriesInheritanceRoot);

		//if filter had categories that doesn't exists or not entitled - should return 0 objects. 
		if(count($categories) && !count($categoriesInheritanceRoot))
		{
			$response = new VidiunCategoryUserListResponse();
			$response->totalCount = 0;
			
			return $response;
		}
		
		$categoryVuserFilter = $this->toObject();
		
		$c = VidiunCriteria::create(categoryVuserPeer::OM_CLASS);
		$categoryVuserFilter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		$c->applyFilters();
		
		$list = categoryVuserPeer::doSelect($c);
		$totalCount = $c->getRecordsCount();
		
		$newList = VidiunCategoryUserArray::fromDbArray($list, $responseProfile);
		
		$response = new VidiunCategoryUserListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}

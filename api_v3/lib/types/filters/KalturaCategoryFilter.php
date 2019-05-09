<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunCategoryFilter extends VidiunCategoryBaseFilter
{
	static private $map_between_objects = array
	(
		"freeText" => "_free_text",
		"membersIn" => "_in_members",
		"appearInListEqual" => "_eq_display_in_search",
		"nameOrReferenceIdStartsWith" => "_likex_name_or_reference_id",
		"managerEqual" => "_eq_manager",
		"memberEqual" => "_eq_member",
		"fullNameStartsWithIn" => "_matchor_likex_full_name",
		"ancestorIdIn" => "_in_ancestor_id",
		"idOrInheritedParentIdIn" => "_in_id-inherited_parent_id",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * @var string
	 */
	public $freeText;

	/**
	 * @var string
	 */
	public $membersIn;

	/**
	 * @var string
	 */
	public $nameOrReferenceIdStartsWith;
	
	/**
	 * @var string
	 */
	public $managerEqual;
	
	/**
	 * @var string
	 */
	public $memberEqual;
	
	/**
	 * @var string
	 */
	public $fullNameStartsWithIn;
		
	/**
	 * not includes the category itself (only sub categories)
	 * @var string
	 */
	public $ancestorIdIn;
	
	/**
	 * @var string
	 */
	public $idOrInheritedParentIdIn;

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new categoryFilter();
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		if ($this->orderBy === null)
			$this->orderBy = VidiunCategoryOrderBy::DEPTH_ASC;
			
		$categoryFilter = $this->toObject();
		
		$c = VidiunCriteria::create(categoryPeer::OM_CLASS);
		$categoryFilter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		$dbList = categoryPeer::doSelect($c);
		$totalCount = $c->getRecordsCount();
		
		$list = VidiunCategoryArray::fromDbArray($dbList, $responseProfile);
		
		$response = new VidiunCategoryListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::validateForResponseProfile()
	 */
	public function validateForResponseProfile()
	{
		if(vEntitlementUtils::getEntitlementEnforcement())
		{
			if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENABLE_RESPONSE_PROFILE_USER_CACHE, vCurrentContext::getCurrentPartnerId()))
			{
				VidiunResponseProfileCacher::useUserCache();
				return;
			}
			
			throw new VidiunAPIException(VidiunErrors::CANNOT_LIST_RELATED_ENTITLED_WHEN_ENTITLEMENT_IS_ENABLE, get_class($this));
		}
	}
}

<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunUserFilter extends VidiunUserBaseFilter
{
	
	static private $map_between_objects = array
	(
		"idOrScreenNameStartsWith" => "_likex_puser_id_or_screen_name",
		'firstNameOrLastNameStartsWith' => "_likex_first_name_or_last_name",
		"idEqual" => "_eq_puser_id",
		"idIn" => "_in_puser_id",
		"roleIdsEqual"	=> "_eq_role_ids",
		"roleIdsIn"	=>	"_in_role_ids",
		"permissionNamesMultiLikeAnd" => "_mlikeand_permission_names",
		"permissionNamesMultiLikeOr" => "_mlikeor_permission_names",
	);

	static private $order_by_map = array
	(
		"+id" => "+puser_id",
		"-id" => "-puser_id",
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
		return new vuserFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::toObject()
	 */
	public function toObject ( $object_to_fill = null, $props_to_skip = array() )
	{
		$object_to_fill =  parent::toObject($object_to_fill, $props_to_skip);
		
		if (!is_null($this->loginEnabledEqual)) {
			if ($this->loginEnabledEqual === true)
				$object_to_fill->set('_gt_login_data_id', 0);
				
			if ($this->loginEnabledEqual === false)
				$object_to_fill->set('_ltornull_login_data_id', 0);
		}
		
		return $object_to_fill;		
	}
	
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);
		
		$loginDataIdGreaterOrEqualValue =  $source_object->get('_gt_login_data_id');
		$loginDataIdLessThanOrNullValue =  $source_object->get('_ltornull_login_data_id');
		
		if ($loginDataIdGreaterOrEqualValue === 0) {
			$this->loginEnabledEqual = true;
		}
		else if ($loginDataIdLessThanOrNullValue === 0) {
			$this->loginEnabledEqual = false;
		}				
	}
	
	/**
	 * @var string
	 */
	public $idOrScreenNameStartsWith;

	/**
	 * @var string
	 */
	public $idEqual;

	/**
	 * @var string
	 */
	public $idIn;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $loginEnabledEqual;
	
	/**
	 * @var string
	 */
	public $roleIdEqual;
	
	/**
	 * @var string
	 */
	public $roleIdsEqual;
	
	/**
	 * @var string
	 */
	public $roleIdsIn;
	
	/**
	 * @var string
	 */
	public $firstNameOrLastNameStartsWith;
	
	/**
	 * Permission names filter expression
	 * @var string
	 */
	public $permissionNamesMultiLikeOr;
	
	/**
	 * Permission names filter expression
	 * @var string
	 */
	public $permissionNamesMultiLikeAnd;

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$userFilter = $this->toObject();
		
		$c = VidiunCriteria::create(vuserPeer::OM_CLASS);
		$userFilter->attachToCriteria($c);
		
		if (!is_null($this->roleIdEqual))
		{
			$roleCriteria = new Criteria();
			$roleCriteria->add ( VuserToUserRolePeer::USER_ROLE_ID , $this->roleIdEqual );
			$roleCriteria->addSelectColumn(VuserToUserRolePeer::VUSER_ID);
			$rs = VuserToUserRolePeer::doSelectStmt($roleCriteria);
			$vuserIds = $rs->fetchAll(PDO::FETCH_COLUMN);
						
			$c->add(vuserPeer::ID, $vuserIds, VidiunCriteria::IN);
		}

		$c->addAnd(vuserPeer::PUSER_ID, NULL, VidiunCriteria::ISNOTNULL);
		
		$pager->attachToCriteria($c);
		$list = vuserPeer::doSelect($c);
		
		$totalCount = $c->getRecordsCount();

		$newList = VidiunUserArray::fromDbArray($list, $responseProfile);
		$response = new VidiunUserListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}

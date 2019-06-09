<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunCategoryUserAdvancedFilter extends VidiunSearchItem
{
	/**
	 * @var string
	 */
	public $memberIdEq;
	
	/**
	 * @var string
	 */
	public $memberIdIn;
	
	/**
	 * @var string
	 */
	public $memberPermissionsMatchOr;
	
	/**
	 * @var string
	 */
	public $memberPermissionsMatchAnd;
	
	private static $map_between_objects = array
	(
		"memberPermissionsMatchOr",
		"memberPermissionsMatchAnd",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject ( $obj = null , $props_to_skip = array() )
	{
		if(!$obj)
			$obj = new vCategoryVuserAdvancedFilter();
		
		if (!$this->memberIdEq && !$this->memberIdIn)
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, 'memberIdEq,memberIdIn');
		}
		
		if (!$this->memberPermissionsMatchOr && !$this->memberPermissionsMatchAnd)
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, 'memberIdEq,memberIdIn');
		}
		
		if ($this->memberIdEq)
		{
			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::getCurrentPartnerId(), $this->memberIdEq);
			if (!$vuser)
			{
				throw new VidiunAPIException (VidiunErrors::USER_NOT_FOUND);
			}

			$vuserIds = array($vuser->getId());
			// retrieve categories that the user is a member by a group.
			$vgroupIds = VuserVgroupPeer::retrieveVgroupIdsByVuserId($vuser->getId());
			if (!is_null($vgroupIds) && is_array($vgroupIds))
				$vuserIds = array_merge($vgroupIds, $vuserIds);
			$obj->setMemberIdIn($vuserIds);
		}
		
		if ($this->memberIdIn)
		{
			$vusers = vuserPeer::getVuserByPartnerAndUids(vCurrentContext::getCurrentPartnerId(), explode(',', $this->memberIdIn));
			$vuserIds = array();
			if (!$vusers || !count($vusers))
				throw new VidiunAPIException (VidiunErrors::USER_NOT_FOUND);
			
			foreach($vusers as $vuser)
			{
				$vuserIds[] = $vuser->getId();
			}
			// retrieve categories that the users are members by a group.
			$vgroupIds = VuserVgroupPeer::retrieveVgroupIdsByVuserIds($vuserIds);
			if (!is_null($vgroupIds) && is_array($vgroupIds))
				$vuserIds = array_merge($vgroupIds, $vuserIds);

			$obj->setMemberIdIn($vuserIds);
		}
			
		return parent::toObject($obj, $props_to_skip);
	}
}
<?php


/**
 * Skeleton subclass for performing query and update operations on the 'vuser_to_user_role' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package Core
 * @subpackage model
 */
class VuserToUserRolePeer extends BaseVuserToUserRolePeer implements IRelatedObjectPeer
{
	
	/**
	 * Get objects by vuser and user role IDs
	 * @param int $vuserId
	 * @param int $userRoleId
	 * @return array Array of selected VuserToUserRole Objects
	 */
	public static function getByVuserAndUserRoleIds($vuserId, $userRoleId)
	{
		$c = new Criteria();
		$c->addAnd(self::VUSER_ID, $vuserId, Criteria::EQUAL);
		$c->addAnd(self::USER_ROLE_ID, $userRoleId, Criteria::EQUAL);
		return self::doSelect($c);
	}
	
	public static function getCacheInvalidationKeys()
	{
		return array(array("vuserToUserRole:vuserId=%s", self::VUSER_ID));		
	}
	
	/* (non-PHPdoc)
	 * @see IRelatedObjectPeer::getRootObjects()
	 */
	public function getRootObjects(IRelatedObject $object)
	{
		/* @var $object VuserToUserRole */
		return array(
			$object->getvuser(),
			$object->getUserRole(),
		);
	}

	/* (non-PHPdoc)
	 * @see IRelatedObjectPeer::isReferenced()
	 */
	public function isReferenced(IRelatedObject $object)
	{
		return false;
	}
} // VuserToUserRolePeer

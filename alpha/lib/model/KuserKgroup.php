<?php


/**
 * Skeleton subclass for representing a row from the 'vuser_vgroup' table.
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
class VuserVgroup extends BaseVuserVgroup implements IRelatedObject
{
	const MAX_NUMBER_OF_GROUPS_PER_USER = 1024;
	const GROUP_USER_CREATION_MODE = 'creation_mode';
	const GROUP_USER_ROLE = 'user_role';

	public function setPuserId($puserId)
	{
		if ( self::getPuserId() == $puserId )  // same value - don't set for nothing
			return;

		parent::setPuserId($puserId);

		$partnerId = vCurrentContext::getCurrentPartnerId();

		$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $puserId);
		if (!$vuser)
			throw new vCoreException("Invalid user Id [{$puserId}]", vCoreException::INVALID_USER_ID );

		parent::setVuserId($vuser->getId());
	}

	public function setPgroupId($pgroupId)
	{
		if ( self::getPgroupId() == $pgroupId )  // same value - don't set for nothing
			return;

		parent::setPgroupId($pgroupId);

		$partnerId = vCurrentContext::getCurrentPartnerId();

		$vgroup = vuserPeer::getVuserByPartnerAndUid($partnerId, $pgroupId, false, VuserType::GROUP);
		if (!$vgroup)
			throw new vCoreException("Invalid group Id [{$pgroupId}]", vCoreException::INVALID_USER_ID );

		parent::setVgroupId($vgroup->getId());
	}

	public function getCacheInvalidationKeys()
	{
		return array("vuserVgroup:vuserId=".strtolower($this->getVuserId()), "vuserVgroup:vgroupId=".strtolower($this->getVgroupId()));
	}

	public function postInsert(PropelPDO $con = null)
	{
		parent::postInsert($con);

		if (!$this->alreadyInSave)
			$this->updateVuserIndex();
	}

	public function postUpdate(PropelPDO $con = null)
	{
		parent::postUpdate($con);

		if (!$this->alreadyInSave)
			$this->updateVuserIndex();
	}

	protected function updateVuserIndex()
	{
		$vuserId = $this->getVuserId();
		$vuser = vuserPeer::retrieveByPK($vuserId);
		if(!$vuser)
			throw new vCoreException('vuser not found');
		$vuser->indexToElastic();
	}

	public function setCreationMode($v)	{$this->putInCustomData (self::GROUP_USER_CREATION_MODE, $v);}

	public function getCreationMode(){return $this->getFromCustomData(self::GROUP_USER_CREATION_MODE,
		null, GroupUserCreationMode::MANUAL);}

	public function setUserRole($v)
	{
		$this->putInCustomData (self::GROUP_USER_ROLE, $v);
	}

	public function getUserRole()
	{
		return $this->getFromCustomData(self::GROUP_USER_ROLE, null, GroupUserRole::MEMBER);
	}
}

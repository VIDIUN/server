<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vUserRoleCondition extends vCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::USER_ROLE);
		parent::__construct($not);
	}
	
	/**
	 * @var string
	 */
	protected $roleIds;

	/**
	 * @param string $roleIds
	 */
	public function setRoleIds($roleIds)
	{
		$this->roleIds = $roleIds;
	}

	/**
	 * @return string
	 */
	public function getRoleIds()
	{
		return $this->roleIds;
	}

	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		$partner = PartnerPeer::retrieveByPK(vCurrentContext::$vs_partner_id);
		$roleIds = vPermissionManager::getRoleIds($partner, vCurrentContext::getCurrentVsVuser());
		$conditionRoleIds = array_map('trim', explode(',', $this->roleIds));

		if (!is_array($roleIds))
			$roleIds = array();

		foreach($roleIds as $roleId)
		{
			if (!in_array($roleId, $conditionRoleIds))
			{
				return false;
			}
		}
		return true;
	}

	/* (non-PHPdoc)
	 * @see vCondition::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		return false;
	}
}

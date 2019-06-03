<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vAuthenticatedCondition extends vCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::AUTHENTICATED);
		parent::__construct($not);
	}
	
	/**
	 * The privelege needed to remove the restriction
	 * 
	 * @var array
	 */
	protected $privileges = array(vs::PRIVILEGE_VIEW, vs::PRIVILEGE_VIEW_ENTRY_OF_PLAYLIST);
	
	/**
	 * @param array $privileges
	 */
	public function setPrivileges(array $privileges)
	{
		$this->privileges = $privileges;
	}
	
	/**
	 * @return array
	 */
	function getPrivileges()
	{
		return $this->privileges;
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		if (!$scope->getVs() || (!$scope->getVs() instanceof vs))
			return false;
		
		if ($scope->getVs()->isAdmin())
			return true;
		
		VidiunLog::debug(print_r($this->privileges, true));
		foreach($this->privileges as $privilege)
		{
			if(is_object($privilege))
				$privilege = $privilege->getValue();
				
			VidiunLog::debug("Checking privilege [$privilege] with entry [".$scope->getEntryId()."]");
			if($scope->getVs()->verifyPrivileges($privilege, $scope->getEntryId()))
			{
				VidiunLog::debug("Privilege [$privilege] verified");
				return true;
			}
		}

		VidiunLog::debug("No privilege verified");
		return false;
	}

	/* (non-PHPdoc)
	 * @see vCondition::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		// the VS type and privileges are part of the cache key
		return false;
	}
}

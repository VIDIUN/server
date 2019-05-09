<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vOrCondition extends vCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::OR_OPERATOR);
		parent::__construct($not);
	}
	
	/**
	 * The privelege needed to remove the restriction
	 * 
	 * @var array
	 */
	protected $conditions = array();
	
	/**
	 * @return the $conditions
	 */
	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * @param array $conditions
	 */
	public function setConditions($conditions)
	{
		$this->conditions = $conditions;
	}

	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		foreach($this->conditions as $condition)
		{
			/* @var $condition vCondition */
			if($condition->fulfilled($scope))
			{
				return true;
			}
		}

		return false;
	}

	/* (non-PHPdoc)
	 * @see vCondition::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		foreach($this->conditions as $condition)
		{
			/* @var $condition vCondition */
			if($condition->shouldDisableCache($scope))
			{
				return true;
			}
		}
		
		return false;
	}
}

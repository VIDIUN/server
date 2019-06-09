<?php
/**
 * @package Core
 * @subpackage model.data
 * @abstract
 */
abstract class vCondition 
{
	/**
	 * @var int ConditionType
	 */
	protected $type;
	
	/**
	 * @var string
	 */
	protected $description;
	
	/**
	 * @var bool
	 */
	protected $not = false;

	/**
	 * @var array
	 */
	protected $extraProperties;

	public function __construct($not = false)
	{
		$this->setNot($not);
		$this->extraProperties = array();
	}
	
	/**
	 * Enable changing the condition attributes according to additional data in the scope
	 */
	protected function applyDynamicValues(vScope $scope)
	{
	}
	
	/**
	 * @param vScope $scope
	 * @return bool
	 */
	abstract protected function internalFulfilled(vScope $scope);
	
	/**
	 * @param vScope $scope
	 * @return bool
	 */
	final public function fulfilled(vScope $scope)
	{
		$this->applyDynamicValues($scope);
		return $this->calcNot($this->internalFulfilled($scope));
	}
	
	/**
	 * @return int ConditionType
	 */
	public function getType() 
	{
		return $this->type;
	}

	/**
	 * @param int $type ConditionType
	 */
	protected function setType($type) 
	{
		$this->type = $type;
	}

	/**
	 * @return string $description
	 */
	public function getDescription() 
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) 
	{
		$this->description = $description;
	}
	
	/**
	 * @return bool
	 */
	public function getNot() 
	{
		return $this->not;
	}

	/**
	 * @param bool $not
	 */
	public function setNot($not) 
	{
		$this->not = $not;
	}

	/**
	 * Calculates the NOT operator
	 * @param bool
	 * @return bool
	 */
	private function calcNot($value) 
	{
		return $this->not ? !$value : $value;
	}

	/**
	 * @param vScope $scope
	 * @return bool
	 */
	public function shouldDisableCache($scope)
	{
		return true;
	}

	public function getExtraProperties()
	{
		if (is_null($this->extraProperties))
		{
			return array();
		}
		return $this->extraProperties;
	}

	public function setExtraProperties($fieldName, $value)
	{
		$this->extraProperties[$fieldName] = $value;
	}


}

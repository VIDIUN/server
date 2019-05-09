<?php
/**
 * @package Core
 * @subpackage model.data
 * @abstract
 */
abstract class vCompareCondition extends vCondition
{
	/**
	 * Value to evaluate against the field and operator
	 * @var vIntegerValue
	 */
	protected $value;
	
	/**
	 * Comparing operator, enum of searchConditionComparison
	 * @var int
	 */
	protected $comparison;

	/**
	 * @return vIntegerValue
	 */
	public function getValue() 
	{
		return $this->value;
	}

	/**
	 * Comparing operator, enum of searchConditionComparison
	 * @return int
	 */
	public function getComparison() 
	{
		return $this->comparison;
	}

	/**
	 * @param vIntegerValue $value
	 */
	public function setValue(vIntegerValue $value) 
	{
		$this->value = $value;
	}

	/**
	 * Comparing operator, enum of searchConditionComparison
	 * @param int $comparison
	 */
	public function setComparison($comparison) 
	{
		$this->comparison = $comparison;
	}

	/**
	 * Return single integer or array of integers
	 * @param vScope $scope
	 * @return int|array<int> the field content
	 */
	abstract public function getFieldValue(vScope $scope);
	
	/**
	 * @return int
	 */
	function getIntegerValue($scope)
	{
		if(is_object($this->value))
		{
			if($this->value instanceof vIntegerField)
				$this->value->setScope($scope);
				
			return $this->value->getValue();
		}
		
		return intval($this);
	}
	
	/**
	 * @param int $field
	 * @param int $value
	 * @return bool
	 */
	protected function fieldFulfilled($field, $value)
	{
		switch($this->comparison)
		{
			case searchConditionComparison::GREATER_THAN:
				VidiunLog::debug("Compares field[$field] > value[$value]");
				return ($field > $value);
				
			case searchConditionComparison::GREATER_THAN_OR_EQUAL:
				VidiunLog::debug("Compares field[$field] >= value[$value]");
				return ($field >= $value);
				
			case searchConditionComparison::LESS_THAN:
				VidiunLog::debug("Compares field[$field] < value[$value]");
				return ($field < $value);
				
			case searchConditionComparison::LESS_THAN_OR_EQUAL:
				VidiunLog::debug("Compares field[$field] <= value[$value]");
				return ($field <= $value);
				
			case searchConditionComparison::EQUAL:
			default:
				VidiunLog::debug("Compares field[$field] == value[$value]");
				return ($field == $value);
		}
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		$field = $this->getFieldValue($scope);
		$value = $this->getIntegerValue($scope);
		
		VidiunLog::debug("Copares field [$field] to value [$value]");
		if (is_null($value))
		{
			VidiunLog::debug("Value is null, condition is true");
			return true;
		}
		
		if (!$field)
		{
			VidiunLog::debug("Field is empty, condition is false");
			return false;
		}

		if(is_array($field))
		{
			foreach($field as $fieldItem)
			{
				if(!$this->fieldFulfilled($fieldItem, $value))
				{
					VidiunLog::debug("Field item [$fieldItem] does not fulfill, condition is false");
					return false;
				}
			}
			VidiunLog::debug("All field items fulfilled, condition is true");
			return true;
		}
		
		return $this->fieldFulfilled($field, $value);
	}

	/* (non-PHPdoc)
	 * @see vCondition::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		return (is_object($this->value) && $this->value->shouldDisableCache($scope)) ||
			$this->shouldFieldDisableCache($scope);
	}

	/**
	 * @param vScope $scope
	 * @return bool
	 */
	public function shouldFieldDisableCache($scope)
	{
		return true;
	}
}

<?php
/**
 * @package Core
 * @subpackage model.data
 * @abstract
 */
abstract class vMatchCondition extends vCondition
{
	/**
	 * @var array<vStringValue>
	 */
	protected $values;
	
	/**
	 * @var MatchConditionType - default is MATCH_ALL in case the field has multiple values, enforce matching all of them 
	 */
	protected $matchType = MatchConditionType::MATCH_ALL;
	
	/**
	 * @var array
	 */
	protected $dynamicValues;
	
	/**
	 * @param array $values
	 */
	function setValues(array $values)
	{
		$vStringValues = $values;
		foreach($values as $index => $value)
			if(is_string($value))
				$vStringValues[$index] = new vStringValue($value);
				
		$this->values = $vStringValues;
	}
	
	/**
	 * @return array
	 */
	function getValues()
	{
		return $this->values;
	}
	
	/**
	 * @return MatchConditionType
	 */
	public function getMatchType()
	{
		return $this->matchType;
	}
	
	/**
	 * @param MatchConditionType $matchType
	 */
	public function setMatchType($matchType)
	{
		$this->matchType = $matchType;
	}
	
	

	/* (non-PHPdoc)
	 * @see vCondition::applyDynamicValues()
	 */
	protected function applyDynamicValues(vScope $scope)
	{
		parent::applyDynamicValues($scope);
		$this->dynamicValues = $scope->getDynamicValues('{', '}');
	}
	
	/**
	 * @param vScope $scope
	 * @return array<string>
	 */
	function getStringValues($scope = null)
	{
		if(!is_array($this->values))
			return array();
			
		$values = array();
		$dynamicValuesKeys = null;
		if(is_array($this->dynamicValues) && count($this->dynamicValues))
			$dynamicValuesKeys = array_keys($this->dynamicValues);
		
		foreach($this->values as $value)
		{
			/* @var $value vStringValue */
			$calculatedValue = null;
			if(is_object($value))
			{
				if($scope && $value instanceof vStringField)
					$value->setScope($scope);
				
				$calculatedValue = $value->getValue();
			}
			else
			{
				$calculatedValue = strval($value);
			}
			
			if($dynamicValuesKeys)
				$calculatedValue = str_replace($dynamicValuesKeys, $this->dynamicValues, $calculatedValue);
		
			$values[] = $calculatedValue;
		}
		
		return $values;
	}
	
	/**
	 * @param vScope $scope
	 * @return string the field content
	 */
	abstract public function getFieldValue(vScope $scope);
	
	/**
	 * @param string $field
	 * @param string $value
	 */
	protected function matches($field, $value)
	{
		return ($field === $value);
	}
	
	/**
	 * @param string $field
	 * @param array $values
	 * @return boolean
	 */
	public function fieldFulfilled($field, $values)
	{
		if(in_array($field, $values))
		{
			VidiunLog::debug("[$this->description] Field found in the values list, condition is true");
			return true;
		}
		
		foreach($values as $value)
		{
			if($this->matches($field, $value))
			{
				VidiunLog::debug("[$this->description] Field [$field] matches value [$value], condition is true");
				return true;
			}
		}
			
		VidiunLog::debug("[$this->description] No match found, condition is false");
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		$field = $this->getFieldValue($scope);
		$values = $this->getStringValues($scope);
		
		VidiunLog::debug("[$this->description] Matches field [" . print_r($field, true) . "] to values [" . print_r($values, true) . "]");
		if (!count($values))
		{
			VidiunLog::debug("[$this->description] No values found, condition is true");
			return true;
		}
		
		if (is_null($field))
		{
			VidiunLog::debug("[$this->description] Field is empty, condition is false");
			return false;
		}

		if(is_array($field))
		{
			if ($this->matchType == MatchConditionType::MATCH_ALL)
			{
				foreach($field as $fieldItem)
				{
					if(!$this->fieldFulfilled($fieldItem, $values))
					{
						VidiunLog::debug("[$this->description] Field item [$fieldItem] does not fulfill, condition is false");
						return false;
					}
				}
				VidiunLog::debug("[$this->description] All field items fulfilled, condition is true");
				return true;
			}
			
			foreach($field as $fieldItem)
			{
				if($this->fieldFulfilled($fieldItem, $values))
				{
					VidiunLog::debug("[$this->description] Field item [$fieldItem] fulfill, condition is true");
					return true;
				}
			}
			VidiunLog::debug("[$this->description] None of the field items fulfilled, condition is false");
			return false;
		}
		
		return $this->fieldFulfilled($field, $values);
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		if(is_array($this->values))
		{
			foreach($this->values as $value)
			{
				if (is_object($value) && $value->shouldDisableCache($scope))
				{
					return true;
				}
			}
		}
		
		return $this->shouldFieldDisableCache($scope);
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

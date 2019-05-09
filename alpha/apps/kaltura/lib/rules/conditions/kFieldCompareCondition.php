<?php

/**
 * @package Core
 * @subpackage model.data
 */
class vFieldCompareCondition extends vCompareCondition
{
	/**
	 * The field to evaluate against the values
	 * @var vIntegerField
	 */
	private $field;

	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::FIELD_COMPARE);
		parent::__construct($not);
	}
	
	/* (non-PHPdoc)
	 * @see vMatchCondition::getFieldValue()
	 */
	public function getFieldValue(vScope $scope)
	{
		$this->field->setScope($scope);
		return $this->field->getValue();
	}
	
	/**
	 * @return vIntegerField
	 */
	public function getField() 
	{
		return $this->field;
	}

	/**
	 * @param vIntegerField $field
	 */
	public function setField(vIntegerField $field) 
	{
		$this->field = $field;
	}
	
	/* (non-PHPdoc)
	 * @see vCompareCondition::shouldFieldDisableCache()
	 */
	public function shouldFieldDisableCache($scope)
	{
		return $this->field->shouldDisableCache($scope);
	}	
}

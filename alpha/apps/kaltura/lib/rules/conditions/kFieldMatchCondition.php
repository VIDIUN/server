<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vFieldMatchCondition extends vMatchCondition
{
	/**
	 * The field to evaluate against the values
	 * @var vStringField
	 */
	private $field;

	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::FIELD_MATCH);
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
	 * @return vStringField
	 */
	public function getField() 
	{
		return $this->field;
	}

	/**
	 * @param vStringField $field
	 */
	public function setField(vStringField $field) 
	{
		$this->field = $field;
	}

	/* (non-PHPdoc)
	 * @see vMatchCondition::shouldFieldDisableCache()
	 */
	public function shouldFieldDisableCache($scope)
	{
		return $this->field->shouldDisableCache($scope);
	}	
}

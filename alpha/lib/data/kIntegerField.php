<?php

/**
 * Base abstraction for realtime calculated integer value 
 * @package Core
 * @subpackage model.data
 */
abstract class vIntegerField extends vIntegerValue implements IScopeField
{
	/**
	 * @var vScope
	 */
	protected $scope = null;
	
	/**
	 * Calculates the value at realtime
	 * @param vScope $scope
	 * @return int $value
	 */
	abstract protected function getFieldValue(vScope $scope = null);
	
	/* (non-PHPdoc)
	 * @see vIntegerValue::getValue()
	 */
	public function getValue() 
	{
		return $this->getFieldValue($this->scope);
	}
	
	/**
	 * @param vScope $scope
	 */
	public function setScope(vScope $scope) 
	{
		$this->scope = $scope;
	}

	/* (non-PHPdoc)
	 * @see vIntegerValue::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		return true;
	}
}
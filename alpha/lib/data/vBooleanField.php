<?php

/**
 * Base abstraction for realtime calculated boolean value 
 * @package Core
 * @subpackage model.data
 */
abstract class vBooleanField extends vBooleanValue implements IScopeField
{
	/**
	 * @var vScope
	 */
	protected $scope = null;
	
	/**
	 * Calculates the value at realtime
	 * @param vScope $scope
	 * @return bool $value
	 */
	abstract protected function getFieldValue(vScope $scope = null);
	
	/* (non-PHPdoc)
	 * @see vBooleanValue::getValue()
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
	 * @see vBooleanField::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		return true;
	}
}
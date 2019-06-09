<?php

/**
 * Base abstraction for realtime calculated string value 
 * @package Core
 * @subpackage model.data
 */
abstract class vStringField extends vStringValue implements IScopeField
{
	/**
	 * @var vScope
	 */
	protected $scope = null;
	
	/**
	 * Calculates the value at realtime
	 * @param vScope $scope
	 * @return string $value
	 */
	abstract protected function getFieldValue(vScope $scope = null);
	
	/* (non-PHPdoc)
	 * @see vStringValue::getValue()
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
	 * @see vStringValue::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		return true;
	}
}
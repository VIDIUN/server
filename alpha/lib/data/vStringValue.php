<?php

/**
 * Base abstraction for string value, constant or calculated that retrieved from the API 
 * @package Core
 * @subpackage model.data
 */
class vStringValue extends vValue
{
	/**
	 * @param string $value
	 */
	public function __construct($value = null) 
	{
		$this->value = $value;
	}
	
	/**
	 * @return string $value
	 */
	public function getValue() 
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value) 
	{
		$this->value = $value;
	}

	/**
	 * @param vScope $scope
	 * @return bool
	 */
	public function shouldDisableCache($scope)
	{
		return false;
	}
}
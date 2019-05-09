<?php

/**
 * Calculates the current time on server 
 * @package Core
 * @subpackage model.data
 */
class vTimeContextField extends vIntegerField
{
	/**
	 * Time offset in seconds since current time
	 * @var int
	 */
	protected $offset;
	
	/* (non-PHPdoc)
	 * @see vIntegerField::getFieldValue()
	 */
	protected function getFieldValue(vScope $scope = null)
	{
		if(!$scope)
			$scope = new vScope();
			
		return $scope->getTime() + $this->offset;
	}
	
	/**
	 * @return int $offset
	 */
	public function getOffset() 
	{
		return $this->offset;
	}

	/**
	 * @param int $offset
	 */
	public function setOffset($offset) 
	{
		$this->offset = $offset;
	}

	
	/* (non-PHPdoc)
	 * @see vIntegerValue::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		// the caching is for a limited time, so we can cache the result even when time fields are used
		return false;
	}	
}
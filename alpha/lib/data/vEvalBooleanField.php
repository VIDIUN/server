<?php
/**
 * Evaluates PHP statement, depends on the execution context
 *  
 * @package Core
 * @subpackage model.data
 */
class vEvalBooleanField extends vBooleanField
{
	/**
	 * PHP code
	 * @var bool
	 */
	protected $code;
	
	/* (non-PHPdoc)
	 * @see vBooleanField::getFieldValue()
	 */
	protected function getFieldValue(vScope $scope = null) 
	{
		if(!$scope)
			return null;
			
		/* @var $scope vEventScope */
		if(strpos($this->code, ';') !== false)
			throw new vCoreException("Evaluated code may be simple value only");
		
		VidiunLog::debug("Evaluating code [$this->code]" . ($this->description ? " for description [$this->description]" : ''));
		return eval("return (bool)({$this->code});");
	}
	
	/**
	 * @return bool $code
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param bool $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	
	
}
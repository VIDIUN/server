<?php
/**
 * Evaluates PHP statement, depends on the execution context
 *  
 * @package Core
 * @subpackage model.data
 */
class vEvalStringField extends vStringField
{
	/**
	 * PHP code
	 * @var string
	 */
	protected $code;
	
	/* (non-PHPdoc)
	 * @see vStringField::getFieldValue()
	 */
	protected function getFieldValue(vScope $scope = null) 
	{
		if(!$scope || !$this->code)
			return null;
		
		if(strpos($this->code, ';') !== false)
			throw new vCoreException("Evaluated code may be simple value only");
			
		VidiunLog::debug("Evaluating code [$this->code]" . ($this->description ? " for description [$this->description]" : ''));
		return eval("return strval({$this->code});");
	}
	
	/**
	 * @return string $code
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	
	
}
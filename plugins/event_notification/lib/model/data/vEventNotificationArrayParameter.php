<?php
/**
 * @package plugins.eventNotification
 * @subpackage model.data
 */
class vEventNotificationArrayParameter extends vEventNotificationParameter
{
	/**
	 * @var array
	 */
	protected $values;
	
	/**
	 * Used to restrict the values to close list
	 * @var array<vStringValue>
	 */
	protected $allowedValues;

	/* (non-PHPdoc)
	 * @see vEventNotificationParameter::getValue()
	 */
	public function getValue()
	{
		if(!$this->values)
			return null;
			
		$value = new vStringValue();
		$value->setValue(implode(',', $this->values));
		
		return $value;
	}
	
	/**
	 * @return array $values
	 */
	public function getValues()
	{
		return $this->values;
	}

	/**
	 * @return array $allowedValues
	 */
	public function getAllowedValues()
	{
		return $this->allowedValues;
	}

	/**
	 * @param array $values
	 */
	public function setValues($values)
	{
		$this->values = $values;
	}

	/**
	 * @param array $allowedValues
	 */
	public function setAllowedValues($allowedValues)
	{
		$this->allowedValues = $allowedValues;
	}
}
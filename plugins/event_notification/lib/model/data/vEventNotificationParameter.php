<?php
/**
 * @package plugins.eventNotification
 * @subpackage model.data
 */
class vEventNotificationParameter
{
	/**
	 * The key to be replaced in the content
	 * @var string
	 */
	protected $key;

	/**
	 * @var string
	 */
	protected $description;
	
	/**
	 * The value that replace the key 
	 * @var vStringValue
	 */
	protected $value;
	
	/**
	 * @return the $key
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @return vStringValue $value
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return string $description
	 */
	public function getDescription() 
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) 
	{
		$this->description = $description;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @param vStringValue $value
	 */
	public function setValue(vStringValue $value)
	{
		$this->value = $value;
	}
}
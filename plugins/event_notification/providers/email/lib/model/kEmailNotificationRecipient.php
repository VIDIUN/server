<?php
/**
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class vEmailNotificationRecipient
{
	/**
	 * Recipient e-mail address
	 * @var vStringValue
	 */
	protected $email;
	
	/**
	 * Recipient name
	 * @var vStringValue
	 */
	protected $name;
	
	/**
	 * @return vStringValue $email
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return vStringValue $name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param vStringValue $email
	 */
	public function setEmail(vStringValue $email)
	{
		$this->email = $email;
	}

	/**
	 * @param vStringValue $name
	 */
	public function setName(vStringValue $name)
	{
		$this->name = $name;
	}
}
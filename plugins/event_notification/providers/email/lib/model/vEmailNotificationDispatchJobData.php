<?php
/**
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class vEmailNotificationDispatchJobData extends vEventNotificationDispatchJobData
{
	/**
	 * Define the email sender email
	 * 
	 * @var string
	 */
	private $fromEmail;
	
	/**
	 * Define the email sender name
	 * 
	 * @var string
	 */
	private $fromName;
	
	/**
	 * Email recipient emails and names, key is mail address and value is the name
	 * 
	 * @var vEmailNotificationRecipientJobData
	 */
	private $to;
	
	/**
	 * Email cc emails and names, key is mail address and value is the name
	 * 
	 * @var vEmailNotificationRecipientJobData
	 */
	private $cc;
	
	/**
	 * Email bcc emails and names, key is mail address and value is the name
	 * 
	 * @var vEmailNotificationRecipientJobData
	 */
	private $bcc;
	
	/**
	 * Email addresses that a reading confirmation will be sent to
	 * 
	 * @var vEmailNotificationRecipientJobData
	 */
	private $replyTo;
	
	/**
	 * Define the email priority of enum EmailNotificationTemplatePriority
	 * 
	 * @var int
	 */
	private $priority;
	
	/**
	 * Email address that a reading confirmation will be sent
	 * 
	 * @var string
	 */
	private $confirmReadingTo;
	
	/**
	 * Hostname to use in Message-Id and Received headers and as default HELO string. 
	 * If empty, the value returned by SERVER_NAME is used or 'localhost.localdomain'.
	 * 
	 * @var string
	 */
	private $hostname;
	
	/**
	 * Sets the message ID to be used in the Message-Id header.
	 * If empty, a unique id will be generated.
	 * 
	 * @var string
	 */
	private $messageID;
	
	/**
	 * Adds a e-mail custom header
	 * 
	 * @var array<key,value>
	 */
	private $customHeaders;
	
	/**
	 * @return the $fromEmail
	 */
	public function getFromEmail() 
	{
		return $this->fromEmail;
	}

	/**
	 * @return the $fromName
	 */
	public function getFromName()  
	{
		return $this->fromName;
	}

	/**
	 * @param string $fromEmail
	 */
	public function setFromEmail($fromEmail)  
	{
		$this->fromEmail = $fromEmail;
	}

	/**
	 * @param string $fromName
	 */
	public function setFromName($fromName)  
	{
		$this->fromName = $fromName;
	}

	/**
	 * @return int $priority of enum EmailNotificationTemplatePriority
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * @param int $priority of enum EmailNotificationTemplatePriority
	 */
	public function setPriority($priority)
	{
		$this->priority = $priority;
	}
	
	/**
	 * @return vEmailNotificationRecipientJobData $to
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @return vEmailNotificationRecipientJobData $cc
	 */
	public function getCc()
	{
		return $this->cc;
	}

	/**
	 * @return vEmailNotificationRecipientJobData $bcc
	 */
	public function getBcc()
	{
		return $this->bcc;
	}

	/**
	 * @param vEmailNotificationRecipientJobData $to
	 */
	public function setTo(vEmailNotificationRecipientJobData $to)
	{
		$this->to = $to;
	}

	/**
	 * @param vEmailNotificationRecipientJobData $cc
	 */
	public function setCc(vEmailNotificationRecipientJobData $cc)
	{
		$this->cc = $cc;
	}

	/**
	 * @param vEmailNotificationRecipientJobData $bcc
	 */
	public function setBcc(vEmailNotificationRecipientJobData $bcc)
	{
		$this->bcc = $bcc;
	}
	
	/**
	 * @return string $confirmReadingTo
	 */
	public function getConfirmReadingTo()
	{
		return $this->confirmReadingTo;
	}

	/**
	 * @return vEmailNotificationRecipientJobData $replyTo
	 */
	public function getReplyTo()
	{
		return $this->replyTo;
	}

	/**
	 * @return string $hostname
	 */
	public function getHostname()
	{
		return $this->hostname;
	}

	/**
	 * @return string $messageID
	 */
	public function getMessageID()
	{
		return $this->messageID;
	}

	/**
	 * @return array<key,value> $customHeaders
	 */
	public function getCustomHeaders()
	{
		return $this->customHeaders;
	}

	/**
	 * @param string $confirmReadingTo
	 */
	public function setConfirmReadingTo($confirmReadingTo)
	{
		$this->confirmReadingTo = $confirmReadingTo;
	}

	/**
	 * @param vEmailNotificationRecipientJobData $replyTo
	 */
	public function setReplyTo(vEmailNotificationRecipientJobData $replyTo)
	{
		$this->replyTo = $replyTo;
	}

	/**
	 * @param string $hostname
	 */
	public function setHostname($hostname)
	{
		$this->hostname = $hostname;
	}

	/**
	 * @param string $messageID
	 */
	public function setMessageID($messageID)
	{
		$this->messageID = $messageID;
	}

	/**
	 * @param array<key,value> $customHeaders
	 */
	public function setCustomHeaders(array $customHeaders)
	{
		$this->customHeaders = $customHeaders;
	}
}
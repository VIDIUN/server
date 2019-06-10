<?php
/**
 * Engine which retrieves a static list of email recipients
 * 
 * @package plugins.emailNotification
 * @subpackage Scheduler
 */
class VEmailNotificationStaticRecipientEngine extends VEmailNotificationRecipientEngine
{
	/* (non-PHPdoc)
	 * @see VEmailNotificationRecipientEngine::getRecipients()
	 */
	function getRecipients(array $contentParameters) 
	{
		$recipients = array();
		foreach ($this->recipientJobData->emailRecipients as $emailRecipient)
		{
			/* var $emailRecipient VidiunKeyValue */
			$email = $emailRecipient->key;
			$name = $emailRecipient->value;
			if(is_array($contentParameters) && count($contentParameters))
			{
				$email = str_replace(array_keys($contentParameters), $contentParameters, $email);
				$name = str_replace(array_keys($contentParameters), $contentParameters, $name);
			}
			$recipients[$email] = $name;
		}
		
		return $recipients;
	}
}
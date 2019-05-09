<?php
/**
 * Abstract engine which retrieves a list of the email notification recipients.
 * 
 * @package plugins.emailNotification
 * @subpackage Scheduler
 */
abstract class VEmailNotificationRecipientEngine
{
	/**
	 * Job data for the email notification recipients
	 * @var VidiunEmailNotificationRecipientJobData
	 */
	protected $recipientJobData;
	
	public function __construct(VidiunEmailNotificationRecipientJobData $recipientJobData)
	{
		$this->recipientJobData = $recipientJobData;
		
	}
	
	/**
	 * Function retrieves instance of recipient job data
	 * @param VidiunEmailNotificationRecipientJobData $recipientJobData
	 * @param VidiunClient $vClient
	 * @return VEmailNotificationRecipientEngine
	 */
	public static function getEmailNotificationRecipientEngine(VidiunEmailNotificationRecipientJobData $recipientJobData)
	{
		return VidiunPluginManager::loadObject('VEmailNotificationRecipientEngine', $recipientJobData->providerType, array($recipientJobData));
	}

	
	/**
	 * Function returns an array of the recipients who should receive the email notification regarding the category
	 * @param array $contentParameters
	 */
	abstract function getRecipients (array $contentParameters);
}
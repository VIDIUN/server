<?php
/**
 * Engine which retrieves a dynamic list of user recipients based on provided filter
 * 
 * @package plugins.emailNotification
 * @subpackage Scheduler
 */
class VEmailNotificationUserRecipientEngine extends  VEmailNotificationRecipientEngine
{
	/* (non-PHPdoc)
	 * @see VEmailNotificationRecipientEngine::getRecipients()
	 */
	function getRecipients(array $contentParameters) {
	    
               $pager = new VidiunFilterPager();
               $pager->pageSize = 500;
		//list users
		$userList = VBatchBase::$vClient->user->listAction($this->recipientJobData->filter, $pager);
		
		$recipients = array();
		foreach ($userList->objects as $user)
		{
			/* @var $user VidiunUser */
			$recipients[$user->email] = $user->firstName. ' ' . $user->lastName;
		}
		
		return $recipients;
	}

	
}
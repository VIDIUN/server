<?php
/**
 * Engine which retrieves the email notification recipients for a category-related event.
 * 
 * @package plugins.emailNotification
 * @subpackage Scheduler
 */
class VEmailNotificationCategoryRecipientEngine extends VEmailNotificationRecipientEngine
{
	/* (non-PHPdoc)
	 * @see VEmailNotificationRecipientEngine::getRecipients()
	 */ 
	function getRecipients(array $contentParameters)
	{
		$recipients = array();
		$pager = new VidiunFilterPager();
		$pager->pageSize = 500;
		$pager->pageIndex = 1;
		$categoryUserIds = array();
		$maxPagesToScan = 2;
		do
		{
			$categoryUserList = VBatchBase::$vClient->categoryUser->listAction($this->recipientJobData->categoryUserFilter, $pager);
			foreach ($categoryUserList->objects as $categoryUser)
				$categoryUserIds[] = $categoryUser->userId;

			$pager->pageIndex ++;
		}
		while (($pager->pageSize == count($categoryUserList->objects)) and ($pager->pageIndex <= $maxPagesToScan));

		if (count($categoryUserIds)==0)
	            return $recipients;

		$pager->pageIndex = 1;
		$userFilter = new VidiunUserFilter();
		$userFilter->idIn = implode(',', $categoryUserIds);
		do
		{
			$userList = VBatchBase::$vClient->user->listAction($userFilter, $pager);
			foreach ($userList->objects as $user)
				$recipients[$user->email] = $user->firstName. ' ' . $user->lastName;

			$pager->pageIndex ++;
		}
		while ($pager->pageSize == count($userList->objects));
		
		return $recipients;
	}
}

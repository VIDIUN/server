<?php
/**
 * Engine which retrieves a list of user assigned to specific group 
 * 
 * @package plugins.emailNotification
 * @subpackage Scheduler
 */
class VEMailNotificationGroupRecipientEngine extends  VEmailNotificationRecipientEngine
{
	/* (non-PHPdoc)
	 * @see VEmailNotificationRecipientEngine::getRecipients()
	 */
	function getRecipients(array $contentParameters) 
	{
		if(is_array($contentParameters) && count($contentParameters))
		{
			$groupId = str_replace(array_keys($contentParameters), $contentParameters, $this->recipientJobData->groupId);
		}
		
		$recipients = array();
		
		$groupUserIds = $this->getGroupUserIds($groupId);
		if(!$groupUserIds)
			return $recipients;
		
		$groupUsers = $this->getUsersByUserIds($groupUserIds);
		if(!$groupUsers)
			return $recipients;
		
		foreach($groupUsers as $groupUser)
		{
			$recipients[$groupUser->email] = $groupUser->firstName. ' ' . $groupUser->lastName;
		}
		
		return $recipients;
	}
	
	private function getUsersByUserIds($userIds)
	{
		$userFilter = new VidiunUserFilter();
		$userFilter->idIn = $userIds;
		
		$pager = new VidiunFilterPager();
		$pager->pageSize = 500;
		
		$users = VBatchBase::$vClient->user->listAction($userFilter, $pager);
		
		if(!($users->totalCount > 0))
			return null;
		
		return $users->objects;
	}

	private function getGroupUserIds($groupId)
	{
		//list users in group
		$groupFilter = new VidiunGroupUserFilter();
		$groupFilter->groupIdEqual = $groupId;
		$pager = new VidiunFilterPager();
		$pager->pageSize = 500;
		
		$groupUserList = VBatchBase::$vClient->groupUser->listAction($groupFilter, $pager);
		
		if(!($groupUserList->totalCount > 0))
			return null;

		
                $groupUserIds = array();
                foreach ($groupUserList->objects as $user)
                {
                        $groupUserIds[]= $user->userId;
                }
                $groupUserIdsString = implode(',',$groupUserIds);

		
		return $groupUserIdsString;
	}
}

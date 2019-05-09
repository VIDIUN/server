<?php
/**
 * @package Scheduler
 * @subpackage Delete
 */
class VDeletingGroupUserEngine extends VDeletingEngine
{
	/* (non-PHPdoc)
	 * @see VDeletingEngine::delete()
	 */
	protected function delete(VidiunFilter $filter)
	{
		return $this->deleteGroupUser($filter);
	}
	
	/**
	 * @param VidiunGroupUserFilter $filter The filter should return the list of groupUsers users that need to be deleted
	 * @return int the number of deleted groupUsers
	 */
	protected function deleteGroupUser(VidiunGroupUserFilter $filter)
	{
		$filter->orderBy = VidiunGroupUserOrderBy::CREATED_AT_ASC;
		
		$groupUsersList = VBatchBase::$vClient->groupUser->listAction($filter, $this->pager);
		if(!$groupUsersList->objects || !count($groupUsersList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($groupUsersList->objects as $groupUser)
		{
			/* @var $groupUser VidiunGroupUser */
			VBatchBase::$vClient->groupUser->delete($groupUser->userId, $groupUser->groupId);
		}
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(is_array($result) && isset($result['code']))
				unset($results[$index]);
				
		return count($results);
	}
}

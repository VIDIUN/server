<?php
/**
 * @package Scheduler
 * @subpackage Delete
 */
class VDeletingUserEntryEngine extends VDeletingEngine
{
	/* (non-PHPdoc)
	 * @see VDeletingEngine::delete()
	 */
	protected function delete(VidiunFilter $filter)
	{
		return $this->deleteUserEntries($filter);
	}
	
	/**
	 * @param VidiunUserEntryFilter $filter The filter should return the list of user entries that need to be deleted
	 * @return int the number of deleted category entries
	 */
	protected function deleteUserEntries(VidiunUserEntryFilter $filter)
	{
		$filter->orderBy = VidiunUserEntryOrderBy::CREATED_AT_ASC;
		
		$userEntryList = VBatchBase::$vClient->userEntry->listAction($filter, $this->pager);
		if(!$userEntryList->objects || !count($userEntryList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($userEntryList->objects as $userEntry)
		{
			/* @var $categoryEntry VidiunUserEntry */
			VBatchBase::$vClient->userEntry->delete($userEntry->id);
		}
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(is_array($result) && isset($result['code']))
				unset($results[$index]);

		return count($results);
	}
}
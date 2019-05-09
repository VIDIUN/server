<?php
/**
 * @package Scheduler
 * @subpackage Delete
 */
class VDeletingCategoryUserEngine extends VDeletingEngine
{
	/* (non-PHPdoc)
	 * @see VDeletingEngine::delete()
	 */
	protected function delete(VidiunFilter $filter)
	{
		return $this->deleteCategoryUsers($filter);
	}
	
	/**
	 * @param VidiunCategoryUserFilter $filter The filter should return the list of category users that need to be deleted
	 * @return int the number of deleted category users
	 */
	protected function deleteCategoryUsers(VidiunCategoryUserFilter $filter)
	{
		$filter->orderBy = VidiunCategoryUserOrderBy::CREATED_AT_ASC;
		
		$categoryUsersList = VBatchBase::$vClient->categoryUser->listAction($filter, $this->pager);
		if(!$categoryUsersList->objects || !count($categoryUsersList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($categoryUsersList->objects as $categoryUser)
		{
			/* @var $categoryUser VidiunCategoryUser */
			VBatchBase::$vClient->categoryUser->delete($categoryUser->categoryId, $categoryUser->userId);
		}
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(is_array($result) && isset($result['code']))
				unset($results[$index]);
				
		return count($results);
	}
}

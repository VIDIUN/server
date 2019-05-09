<?php
/**
 * @package Scheduler
 * @subpackage Index
 */
class KIndexingCategoryUserEngine extends KIndexingEngine
{
	/* (non-PHPdoc)
	 * @see KIndexingEngine::index()
	 */
	protected function index(VidiunFilter $filter, $shouldUpdate)
	{
		return $this->indexCategories($filter, $shouldUpdate);
	}
	
	/**
	 * @param VidiunCategoryUserFilter $filter The filter should return the list of categories that need to be reindexed
	 * @param bool $shouldUpdate Indicates that the category user object columns and attributes values should be recalculated before reindexed
	 * @return int the number of indexed categories
	 */
	protected function indexCategories(VidiunCategoryUserFilter $filter, $shouldUpdate)
	{
		$filter->orderBy = VidiunCategoryUserOrderBy::CREATED_AT_ASC;
		
		$categoryUsersList = VBatchBase::$vClient->categoryUser->listAction($filter, $this->pager);
		if(!$categoryUsersList->objects || !count($categoryUsersList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($categoryUsersList->objects as $categoryUser)
		{
			VBatchBase::$vClient->categoryUser->index($categoryUser->userId, $categoryUser->categoryId, $shouldUpdate);
		}
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(!is_int($result))
				unset($results[$index]);
				
		if(!count($results))
			return 0;
				
		$lastIndexId = end($results);
		$this->setLastIndexId($lastIndexId);
		
		return count($results);
	}
}

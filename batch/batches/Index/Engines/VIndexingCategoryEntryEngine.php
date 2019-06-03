<?php
/**
 * @package Scheduler
 * @subpackage Index
 */
class KIndexingCategoryEntryEngine extends KIndexingEngine
{
	/* (non-PHPdoc)
	 * @see KIndexingEngine::index()
	 */
	protected function index(VidiunFilter $filter, $shouldUpdate)
	{
		return $this->indexCategories($filter, $shouldUpdate);
	}
	
	/**
	 * @param VidiunCategoryEntryFilter $filter The filter should return the list of categories that need to be reindexed
	 * @param bool $shouldUpdate Indicates that the category entry object columns and attributes values should be recalculated before reindexed
	 * @return int the number of indexed categories
	 */
	protected function indexCategories(VidiunCategoryEntryFilter $filter, $shouldUpdate)
	{
		$filter->orderBy = VidiunCategoryEntryOrderBy::CREATED_AT_ASC;
		
		$categoryEntriesList = VBatchBase::$vClient->categoryEntry->listAction($filter, $this->pager);
		if(!$categoryEntriesList->objects || !count($categoryEntriesList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($categoryEntriesList->objects as $categoryEntry)
		{
			VBatchBase::$vClient->categoryEntry->index($categoryEntry->entryId, $categoryEntry->categoryId , $shouldUpdate);
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

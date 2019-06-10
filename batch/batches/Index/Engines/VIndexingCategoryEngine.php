<?php
/**
 * @package Scheduler
 * @subpackage Index
 */
class KIndexingCategoryEngine extends KIndexingEngine
{
	/* (non-PHPdoc)
	 * @see KIndexingEngine::index()
	 */
	protected function index(VidiunFilter $filter, $shouldUpdate)
	{
		return $this->indexCategories($filter, $shouldUpdate);
	}
	
	/**
	 * @param VidiunCategoryFilter $filter The filter should return the list of categories that need to be reindexed
	 * @param bool $shouldUpdate Indicates that the category columns and attributes values should be recalculated before reindexed
	 * @return int the number of indexed categories
	 */
	protected function indexCategories(VidiunCategoryFilter $filter, $shouldUpdate)
	{
		$filter->orderBy = VidiunCategoryOrderBy::DEPTH_ASC . ',' . VidiunCategoryOrderBy::CREATED_AT_ASC;
		
		$categoriesList = VBatchBase::$vClient->category->listAction($filter, $this->pager);
		if(!$categoriesList->objects || !count($categoriesList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($categoriesList->objects as $category)
		{
			VBatchBase::$vClient->category->index($category->id, $shouldUpdate);
		}
		
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(!is_int($result))
				unset($results[$index]);
				
		if(!count($results))
			return 0;
				
		$lastIndexId = end($results);
		$this->setLastIndexId($lastIndexId);

		foreach ($categoriesList->objects as $category)
		{
			if($category->id == $lastIndexId)
				$this->setLastIndexDepth($category->depth);
		}

		return count($results);
	}
}

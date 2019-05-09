<?php
/**
 * @package Scheduler
 * @subpackage Delete
 */
class VDeletingCategoryEntryEngine extends VDeletingEngine
{
	/* (non-PHPdoc)
	 * @see VDeletingEngine::delete()
	 */
	protected function delete(VidiunFilter $filter)
	{
		return $this->deleteCategoryEntries($filter);
	}
	
	/**
	 * @param VidiunCategoryEntryFilter $filter The filter should return the list of category entries that need to be deleted
	 * @return int the number of deleted category entries
	 */
	protected function deleteCategoryEntries(VidiunCategoryEntryFilter $filter)
	{
		$filter->orderBy = VidiunCategoryEntryOrderBy::CREATED_AT_ASC;
		
		$categoryEntriesList = VBatchBase::$vClient->categoryEntry->listAction($filter, $this->pager);
		if(!$categoryEntriesList->objects || !count($categoryEntriesList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($categoryEntriesList->objects as $categoryEntry)
		{
			/* @var $categoryEntry VidiunCategoryEntry */
			VBatchBase::$vClient->categoryEntry->delete($categoryEntry->entryId, $categoryEntry->categoryId);
		}
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(is_array($result) && isset($result['code']))
				unset($results[$index]);

		return count($results);
	}
}

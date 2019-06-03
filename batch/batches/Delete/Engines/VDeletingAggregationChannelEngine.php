<?php
/**
 * @package Scheduler
 * @subpackage Delete
 */
class VDeletingAggregationChannelEngine extends  VDeletingEngine
{
	protected $lastCreatedAt;
	
	protected $publicAggregationChannel;
	protected $excludedCategories;
	
	public function configure($partnerId, $jobData)
	{
		/* @var $jobData VidiunDeleteJobData */
		parent::configure($partnerId, $jobData);

		$this->publicAggregationChannel = $jobData->filter->aggregationCategoriesMultiLikeAnd;
		$this->excludedCategories = $this->retrievePublishingCategories ($jobData->filter);
	}
	
	/* (non-PHPdoc)
	 * @see VDeletingEngine::delete()
	 */
	protected function delete(VidiunFilter $filter) {
		return $this->deleteAggregationCategoryEntries ($filter);
		
	}
	
	protected function deleteAggregationCategoryEntries (VidiunCategoryFilter $filter)
	{
		$entryFilter = new VidiunBaseEntryFilter();
		$entryFilter->categoriesIdsNotContains = $this->excludedCategories;
		$entryFilter->categoriesIdsMatchAnd = $this->publicAggregationChannel . "," . $filter->idNotIn;
		
		$entryFilter->orderBy = VidiunBaseEntryOrderBy::CREATED_AT_ASC;
		if ($this->lastCreatedAt)
		{
			$entryFilter->createdAtGreaterThanOrEqual = $this->lastCreatedAt;
		}
		
		$entryFilter->statusIn = implode (',', array (VidiunEntryStatus::ERROR_CONVERTING, VidiunEntryStatus::ERROR_IMPORTING, VidiunEntryStatus::IMPORT, VidiunEntryStatus::NO_CONTENT, VidiunEntryStatus::READY));
		$entriesList = VBatchBase::$vClient->baseEntry->listAction($entryFilter, $this->pager);
		if(!$entriesList->objects || !count($entriesList->objects))
			return 0;
			
		$this->lastCreatedAt = $entriesList->objects[count ($entriesList->objects) -1];
		VBatchBase::$vClient->startMultiRequest();
		foreach($entriesList->objects as $entry)
		{
			/* @var $entry VidiunBaseEntry */
			VBatchBase::$vClient->categoryEntry->delete($entry->id, $this->publicAggregationChannel);
		}
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(is_array($result) && isset($result['code']))
				unset($results[$index]);

		return count($results);
		
	}
	
	protected function retrievePublishingCategories (VidiunCategoryFilter $filter)
	{
		$categoryPager = new VidiunFilterPager();
		$categoryPager->pageIndex = 1;
		$categoryPager->pageSize = 500;
		
		$categoryIdsToReturn = array ();
		
		$categoryResponse = VBatchBase::$vClient->category->listAction($filter, $categoryPager);
		
		while ($categoryResponse->objects && count($categoryResponse->objects))
		{
			foreach ($categoryResponse->objects as $category)
			{
				$categoryIdsToReturn[] = $category->id;
			}
			
			$categoryPager->pageIndex++;
			$categoryResponse = VBatchBase::$vClient->category->listAction($filter, $categoryPager);
		}
		
		return implode (',', $categoryIdsToReturn);
	}

	
}
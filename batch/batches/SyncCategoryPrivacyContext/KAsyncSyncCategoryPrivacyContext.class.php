<?php
/**
 * @package Scheduler
 * @subpackage MoveCategoryEntries
 */

/**
 * Will sync category privacy context on category entries
 *
 * @package Scheduler
 * @subpackage SyncCategoryPrivacyContext
 */
class VAsyncSyncCategoryPrivacyContext extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::SYNC_CATEGORY_PRIVACY_CONTEXT;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->syncPrivacyContext($job, $job->data);
	}
	
	/**
	 * sync category privacy context on category entries
	 * 
	 * @param VidiunBatchJob $job
	 * @param VidiunSyncCategoryPrivacyContextJobData $data
	 * 
	 * @return VidiunBatchJob
	 */
	protected function syncPrivacyContext(VidiunBatchJob $job, VidiunSyncCategoryPrivacyContextJobData $data)
	{
	    VBatchBase::impersonate($job->partnerId);
	    
	    $this->syncCategoryPrivacyContext($job, $data, $data->categoryId);
		
		VBatchBase::unimpersonate();
		
		$job = $this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED);
		
		return $job;
	}
	
	private function syncCategoryPrivacyContext(VidiunBatchJob $job, VidiunSyncCategoryPrivacyContextJobData $data, $categoryId)
	{
			    
		$categoryEntryPager = $this->getFilterPager();
	    $categoryEntryFilter = new VidiunCategoryEntryFilter();
		$categoryEntryFilter->orderBy = VidiunCategoryEntryOrderBy::CREATED_AT_ASC;
		$categoryEntryFilter->categoryIdEqual = $categoryId;
		if($data->lastUpdatedCategoryEntryCreatedAt)
			$categoryEntryFilter->createdAtGreaterThanOrEqual = $data->lastUpdatedCategoryEntryCreatedAt;		
		$categoryEntryList = VBatchBase::$vClient->categoryEntry->listAction($categoryEntryFilter, $categoryEntryPager);
		
		while($categoryEntryList->objects && count($categoryEntryList->objects))
		{
			VBatchBase::$vClient->startMultiRequest();
			foreach ($categoryEntryList->objects as $categoryEntry) 
			{
				VBatchBase::$vClient->categoryEntry->syncPrivacyContext($categoryEntry->entryId, $categoryEntry->categoryId);				
			}

			VBatchBase::$vClient->doMultiRequest();	
			$data->lastUpdatedCategoryEntryCreatedAt = $categoryEntry->createdAt;
			$categoryEntryPager->pageIndex++;
			
			VBatchBase::unimpersonate();
			$this->updateJob($job, null, VidiunBatchJobStatus::PROCESSING, $data);
			VBatchBase::impersonate($job->partnerId);
							
			$categoryEntryList = VBatchBase::$vClient->categoryEntry->listAction($categoryEntryFilter, $categoryEntryPager);
		}
	}
		
	private function getFilterPager()
	{
		$pager = new VidiunFilterPager();
		$pager->pageSize = 100;
		if(VBatchBase::$taskConfig->params->pageSize)
			$pager->pageSize = VBatchBase::$taskConfig->params->pageSize;
		return $pager;
	}
}

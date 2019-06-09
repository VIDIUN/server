<?php
/**
 * @package Scheduler
 * @subpackage MoveCategoryEntries
 */

/**
 * Will move category entries from source category to destination category
 *
 * @package Scheduler
 * @subpackage MoveCategoryEntries
 */
class VAsyncMoveCategoryEntries extends VJobHandlerWorker
{
	const CATEGORY_ENTRY_ALREADY_EXISTS = 'CATEGORY_ENTRY_ALREADY_EXISTS';
	const INVALID_ENTRY_ID = 'INVALID_ENTRY_ID';
	const CATEGORY_NOT_FOUND = 'CATEGORY_NOT_FOUND';
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::MOVE_CATEGORY_ENTRIES;
	}

	/* (non-PHPdoc)
	 * @see VBatchBase::getPrivileges()
	 */
	protected function getPrivileges()
	{
		return array_merge(parent::getPrivileges(), array(self::PRIVILEGE_BATCH_JOB_TYPE . ':' . self::getType()));
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->move($job, $job->data);
	}
	
	/**
	 * Moves category entries from source category to destination category
	 * 
	 * @param VidiunBatchJob $job
	 * @param VidiunMoveCategoryEntriesJobData $data
	 * 
	 * @return VidiunBatchJob
	 */
	protected function move(VidiunBatchJob $job, VidiunMoveCategoryEntriesJobData $data)
	{
	    VBatchBase::impersonate($job->partnerId);
		
		$job = $this->moveCategory($job, $data);
		VBatchBase::unimpersonate();
		$job = $this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED);
		
		return $job;
	}
	
	/**
	 * Go through all categories tree and call moveEntries
	 * 
	 * @param VidiunBatchJob $job
	 * @param VidiunMoveCategoryEntriesJobData $data
	 * @param int $srcCategoryId Current source category id
	 * 
	 * @return VidiunBatchJob
	 */
	private function moveCategory(VidiunBatchJob $job, VidiunMoveCategoryEntriesJobData $data, $srcCategoryId = null)
	{
	    
		if(is_null($srcCategoryId))
			$srcCategoryId = $data->srcCategoryId;

		$movedEntries = $this->moveEntries($job, $data, $srcCategoryId);

		VBatchBase::unimpersonate();
		$this->updateJob($job, "Moved [$movedEntries] entries", VidiunBatchJobStatus::PROCESSING, $data);
		VBatchBase::impersonate($job->partnerId);
		
		return $job;
	}
	
	private function addCategoryEntries($categoryEntriesList, $destCategoryId, &$entryIds, &$categoryIds)
	{
		VBatchBase::$vClient->startMultiRequest();
		foreach($categoryEntriesList->objects as $oldCategoryEntry)
		{
			/* @var $categoryEntry VidiunCategoryEntry */
			$newCategoryEntry = new VidiunCategoryEntry();
			$newCategoryEntry->entryId = $oldCategoryEntry->entryId;
			$newCategoryEntry->categoryId = $destCategoryId;
			VBatchBase::$vClient->categoryEntry->add($newCategoryEntry);
			$entryIds[] = $oldCategoryEntry->entryId;
			$categoryIds[] = $oldCategoryEntry->categoryId;
		}
		return VBatchBase::$vClient->doMultiRequest();
	}
	
	/**
	 * Moves category entries from source category to destination category
	 */
	private function moveEntries(VidiunBatchJob $job, VidiunMoveCategoryEntriesJobData $data, $srcCategoryId)
	{
		$categoryEntryFilter = new VidiunCategoryEntryFilter();
		$categoryEntryFilter->orderBy = VidiunCategoryEntryOrderBy::CREATED_AT_ASC;
		if($data->moveFromChildren)
			$categoryEntryFilter->categoryFullIdsStartsWith = $data->destCategoryFullIds;
		else
			$categoryEntryFilter->categoryIdEqual = $srcCategoryId;

		$categoryEntryPager = new VidiunFilterPager();
		$categoryEntryPager->pageSize = 100;
		$categoryEntryPager->pageIndex = 1;

		if(VBatchBase::$taskConfig->params && VBatchBase::$taskConfig->params->pageSize)
			$categoryEntryPager->pageSize = VBatchBase::$taskConfig->params->pageSize;
			
		$movedEntries = 0;
		$categoryEntriesList = VBatchBase::$vClient->categoryEntry->listAction($categoryEntryFilter, $categoryEntryPager);
		
		do {
			$entryIds = array();
			$categoryIds = array();

			$addedCategoryEntriesResults = $this->addCategoryEntries($categoryEntriesList, $data->destCategoryId, $entryIds, $categoryIds);

			VBatchBase::$vClient->startMultiRequest();
			foreach($addedCategoryEntriesResults as $index => $addedCategoryEntryResult)
			{
				$code = null;
				if(VBatchBase::$vClient->isError($addedCategoryEntryResult))
				{
					$code = $addedCategoryEntryResult['code'];
					if (!in_array($code, array(self::CATEGORY_ENTRY_ALREADY_EXISTS, self::INVALID_ENTRY_ID)))
					{
						throw new VidiunException($addedCategoryEntryResult['message'], $addedCategoryEntryResult['code'], $addedCategoryEntryResult['args']);
					}
				}
				VBatchBase::$vClient->categoryEntry->delete($entryIds[$index], $categoryIds[$index]);
			}

			$deletedCategoryEntriesResults = VBatchBase::$vClient->doMultiRequest();
			if(is_null($deletedCategoryEntriesResults))
				$deletedCategoryEntriesResults = array();

			foreach($deletedCategoryEntriesResults as $index => $deletedCategoryEntryResult)
			{
				if(is_array($deletedCategoryEntryResult) && isset($deletedCategoryEntryResult['code']))
				{
					VidiunLog::err('error: ' . $deletedCategoryEntryResult['code']);
					unset($deletedCategoryEntriesResults[$index]);
				}
			}

			$movedEntries += count($deletedCategoryEntriesResults);
			$categoryEntriesList = VBatchBase::$vClient->categoryEntry->listAction($categoryEntryFilter, $categoryEntryPager);
		} while( $categoryEntriesList->objects && count($categoryEntriesList->objects) == $categoryEntryPager->pageSize);

		VBatchBase::$vClient->category->index($data->destCategoryId);
		
		return $movedEntries;
	}
}

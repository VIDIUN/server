<?php
/**
 * @package Scheduler
 * @subpackage Index
 */

/**
 * Will index objects in the indexing server
 * according to the suppolied engine type and filter 
 *
 * @package Scheduler
 * @subpackage Index
 */
class VAsyncIndex extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::INDEX;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->indexObjects($job, $job->data);
	}
	
	/**
	 * Will take a single filter and call each item to be indexed 
	 */
	private function indexObjects(VidiunBatchJob $job, VidiunIndexJobData $data)
	{
		$engine = KIndexingEngine::getInstance($job->jobSubType);
		$engine->configure($job->partnerId);
		$advancedFilter = $engine->initAdvancedFilter($data);
		
		$filter = clone $data->filter;
		$filter->advancedSearch = $advancedFilter;

		$continue = true;
		while($continue)
		{
			$indexedObjectsCount = $engine->run($filter, $data->shouldUpdate);
			$continue = (bool) $indexedObjectsCount;
			$lastIndexId = $engine->getLastIndexId();
			$lastIndexDepth = $engine->getLastIndexDepth();
			
			$data->lastIndexId = $lastIndexId;
			$data->lastIndexDepth = $lastIndexDepth;
			$this->updateJob($job, "Indexed $indexedObjectsCount objects", VidiunBatchJobStatus::PROCESSING, $data);
			
			$advancedFilter->indexIdGreaterThan = $lastIndexId;
			$advancedFilter->depthGreaterThanEqual = $lastIndexDepth;
			$filter->advancedSearch = $advancedFilter;
		}
		
		return $this->closeJob($job, null, null, "Index objects finished", VidiunBatchJobStatus::FINISHED);
	}
}

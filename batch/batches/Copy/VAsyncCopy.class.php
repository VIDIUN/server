<?php
/**
 * @package Scheduler
 * @subpackage Copy
 */

/**
 * Will copy objects and add them
 * according to the suppolied engine type and filter 
 *
 * @package Scheduler
 * @subpackage Copy
 */
class VAsyncCopy extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::COPY;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->copyObjects($job, $job->data);
	}
	
	/**
	 * Will take a single filter and call each item to be Copied 
	 */
	private function copyObjects(VidiunBatchJob $job, VidiunCopyJobData $data)
	{
		$engine = VCopyingEngine::getInstance($job->jobSubType);
		$engine->configure($job->partnerId);
	
		$filter = clone $data->filter;
		$advancedFilter = new VidiunIndexAdvancedFilter();
		
		if($data->lastCopyId)
		{
			
			$advancedFilter->indexIdGreaterThan = $data->lastCopyId;
			$filter->advancedSearch = $advancedFilter;
		}
		
		$continue = true;
		while($continue)
		{
			$copiedObjectsCount = $engine->run($filter, $data->templateObject);
			$continue = (bool) $copiedObjectsCount;
			$lastCopyId = $engine->getLastCopyId();
			
			$data->lastCopyId = $lastCopyId;
			$this->updateJob($job, "Copied $copiedObjectsCount objects", VidiunBatchJobStatus::PROCESSING, $data);
			
			$advancedFilter->indexIdGreaterThan = $lastCopyId;
			$filter->advancedSearch = $advancedFilter;
		}
		
		return $this->closeJob($job, null, null, "Copy objects finished", VidiunBatchJobStatus::FINISHED);
	}
}

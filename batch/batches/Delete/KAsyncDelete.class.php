<?php
/**
 * Will delete objects in the deleting server
 * according to the suppolied engine type and filter 
 *
 * @package Scheduler
 * @subpackage Delete
 */
class VAsyncDelete extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::DELETE;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->deleteObjects($job, $job->data);
	}
	
	/**
	 * Will take a single filter and call each item to be deleted 
	 */
	private function deleteObjects(VidiunBatchJob $job, VidiunDeleteJobData $data)
	{
		$engine = VDeletingEngine::getInstance($job->jobSubType);
		$engine->configure($job->partnerId, $data);
	
		$filter = clone $data->filter;
		
		$continue = true;
		while($continue)
		{
			$deletedObjectsCount = $engine->run($filter);
			$continue = (bool) $deletedObjectsCount;
		}
		
		return $this->closeJob($job, null, null, "Delete objects finished", VidiunBatchJobStatus::FINISHED);
	}
}

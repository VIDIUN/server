<?php
/**
 * @package Scheduler
 * @subpackage RecalculateCache
 */

/**
 * Will recalculate cached objects 
 *
 * @package Scheduler
 * @subpackage RecalculateCache
 */
class VAsyncRecalculateCache extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::RECALCULATE_CACHE;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->recalculate($job, $job->data);
	}
	
	private function recalculate(VidiunBatchJob $job, VidiunRecalculateCacheJobData $data)
	{
		$engine = VRecalculateCacheEngine::getInstance($job->jobSubType);
		$recalculatedObjects = $engine->recalculate($data);
		return $this->closeJob($job, null, null, "Recalculated $recalculatedObjects cache objects", VidiunBatchJobStatus::FINISHED);
	}
}

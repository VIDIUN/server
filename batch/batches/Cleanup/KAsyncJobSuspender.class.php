<?php
/**
 * @package Scheduler
 * @subpackage Cleanup
 */

/**
 * Will balance the jobs queue
 *
 * @package Scheduler
 * @subpackage Cleanup
 */
class VAsyncJobSuspender extends VPeriodicWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::CLEANUP;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	*/
	public function run($jobs = null)
	{
		self::$vClient->batch->suspendJobs();
	}
}

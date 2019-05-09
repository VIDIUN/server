<?php
/**
 * @package Scheduler
 * @subpackage Cleanup
 */

/**
 * Will clean from the DB all locked jobs and will mark them as fatal if exeeded max retries
 *
 * @package Scheduler
 * @subpackage Cleanup
 */
class VAsyncDbCleanup extends VPeriodicWorker
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
		self::$vClient->batch->cleanExclusiveJobs();
	}
}

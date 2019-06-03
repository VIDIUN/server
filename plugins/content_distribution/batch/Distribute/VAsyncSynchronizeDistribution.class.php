<?php
/**
 * Synchronize Distribution status and create delayed jobs
 *
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
class VAsyncSynchronizeDistribution extends VPeriodicWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::DISTRIBUTION_SYNC;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	*/
	public function run($jobs = null)
	{
		self::$vClient->contentDistributionBatch->updateSunStatus();
		self::$vClient->contentDistributionBatch->createRequiredJobs();
	}
}

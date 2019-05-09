<?php
/**
 * @package Scheduler
 * @subpackage Cleanup
 */

/**
 * Will clean from the the partner load table all the values according to the actual partner load
 *
 * @package Scheduler
 * @subpackage Cleanup
 */
class VAsyncPartnerLoadCleanup extends VPeriodicWorker
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
		self::$vClient->batch->updatePartnerLoadTable();
	}
}

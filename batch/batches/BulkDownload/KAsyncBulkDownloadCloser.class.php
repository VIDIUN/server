<?php
/**
 * @package Scheduler
 * @subpackage Bulk-Download
 */

/**
 * Will close almost done bulk downloads.
 * The state machine of the job is as follows:
 * 	 	get almost done bulk downloads 
 * 		check converts statuses
 * 		update the bulk status
 *
 * @package Scheduler
 * @subpackage Bulk-Download
 */
class VAsyncBulkDownloadCloser extends VJobCloserWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::BULKDOWNLOAD;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->fetchStatus($job);
	}

	private function fetchStatus(VidiunBatchJob $job)
	{
		if(($job->queueTime + VBatchBase::$taskConfig->params->maxTimeBeforeFail) < time())
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::CLOSER_TIMEOUT, 'Timed out', VidiunBatchJobStatus::FAILED);
		
		return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::ALMOST_DONE);
	}
}

<?php
/**
 * This worker deletes physical files from disk
 *
 * @package Scheduler
 * @subpackage Delete
 */
class VAsyncDeleteFile extends VJobHandlerWorker
{
	public static function getType()
	{
		return VidiunBatchJobType::DELETE_FILE;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		$this->updateJob($job, "File deletion started", VidiunBatchJobStatus::PROCESSING);
		$jobData = $job->data;
		
		/* @var $jobData VidiunDeleteFileJobData */
		$result = unlink($jobData->localFileSyncPath);
		
		if (!$result)
			return $this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, null, "Failed to delete file from disk", VidiunBatchJobStatus::FAILED);
		
		return $this->closeJob($job, null, null, 'File deleted successfully', VidiunBatchJobStatus::FINISHED);
		
	}


}
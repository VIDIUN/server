<?php
class VAsyncStorageExportCloser extends VJobCloserWorker
{
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job) {
		$this->closeStorageExport($job);
		
	}

	public static function getType()
	{
		return VidiunBatchJobType::STORAGE_EXPORT;
	}
	
	protected function closeStorageExport (VidiunBatchJob $job)
	{
		$storageExportEngine = VExportEngine::getInstance($job->jobSubType, $job->partnerId, $job->data);
		
		$closeResult = $storageExportEngine->verifyExportedResource();
		$this->closeJob($job, null, null, null, $closeResult ? VidiunBatchJobStatus::FINISHED : VidiunBatchJobStatus::ALMOST_DONE);
	}
}
<?php
/**
 * Will perform a single deletion of external asset
 *
 * @package Scheduler
 * @subpackage Storage
 */
class VAsyncStorageDelete extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::STORAGE_DELETE;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->delete($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getFilter()
	 */
	protected function getFilter()
	{
		$filter = parent::getFilter();
		
		if(VBatchBase::$taskConfig->params && VBatchBase::$taskConfig->params->minFileSize && is_numeric(VBatchBase::$taskConfig->params->minFileSize))
			$filter->fileSizeGreaterThan = VBatchBase::$taskConfig->params->minFileSize;
		
		if(VBatchBase::$taskConfig->params && VBatchBase::$taskConfig->params->maxFileSize && is_numeric(VBatchBase::$taskConfig->params->maxFileSize))
			$filter->fileSizeLessThan = VBatchBase::$taskConfig->params->maxFileSize;
			
		return $filter;
	}
	
	/**
	 * Will take a single VidiunBatchJob and delete the given file 
	 * 
	 * @param VidiunBatchJob $job
	 * @param VidiunStorageDeleteJobData $data
	 * @return VidiunBatchJob
	 */
	private function delete(VidiunBatchJob $job, VidiunStorageDeleteJobData $data)
	{
        $exportEngine = VExportEngine::getInstance($job->jobSubType, $job->partnerId, $data);
		$this->updateJob($job, "Deleting {$data->destFileSyncStoredPath} from remote storage", VidiunBatchJobStatus::QUEUED);
        
        $exportEngine->delete();
		
		return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED);
	}
	
}

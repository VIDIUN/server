<?php
/**
 * @package Scheduler
 * @subpackage Storage
 */

/**
 * Will export a single file to ftp or scp server 
 *
 * @package Scheduler
 * @subpackage Storage
 */
class VAsyncStorageExport extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::STORAGE_EXPORT;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->export($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getFilter()
	 */
	protected function getFilter()
	{
		$filter = parent::getFilter();
		
		if(VBatchBase::$taskConfig->params)
		{
			if(VBatchBase::$taskConfig->params->minFileSize && is_numeric(VBatchBase::$taskConfig->params->minFileSize))
				$filter->fileSizeGreaterThan = VBatchBase::$taskConfig->params->minFileSize;
			
			if(VBatchBase::$taskConfig->params->maxFileSize && is_numeric(VBatchBase::$taskConfig->params->maxFileSize))
				$filter->fileSizeLessThan = VBatchBase::$taskConfig->params->maxFileSize;
		}
			
		return $filter;
	}
	
	/**
	 * Will take a single VidiunBatchJob and export the given file 
	 * 
	 * @param VidiunBatchJob $job
	 * @param VidiunStorageExportJobData $data
	 * @return VidiunBatchJob
	 */
	protected function export(VidiunBatchJob $job, VidiunStorageExportJobData $data)
	{
		$engine = VExportEngine::getInstance($job->jobSubType, $job->partnerId, $data);
		if(!$engine)
		{
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::ENGINE_NOT_FOUND, "Engine not found", VidiunBatchJobStatus::FAILED);
		}
		$this->updateJob($job, null, VidiunBatchJobStatus::QUEUED);
		$exportResult = $engine->export();

		return $this->closeJob($job, null , null, null, $exportResult ? VidiunBatchJobStatus::FINISHED : VidiunBatchJobStatus::ALMOST_DONE, $data );
	}
}

<?php

class VAsyncDropFolderContentProcessor extends VJobHandlerWorker
{
	/**
	 * @var VidiunDropFolderClientPlugin
	 */
	protected $dropFolderPlugin = null;
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::DROP_FOLDER_CONTENT_PROCESSOR;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		try 
		{
			return $this->process($job, $job->data);
		}
		catch(vTemporaryException $e)
		{
			$this->unimpersonate();
			if($e->getResetJobExecutionAttempts())
				throw $e;
			return $this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, $e->getCode(), "Error: " . $e->getMessage(), VidiunBatchJobStatus::FAILED);
		}
		catch(VidiunClientException $e)
		{
			$this->unimpersonate();
			return $this->closeJob($job, VidiunBatchJobErrorTypes::VIDIUN_CLIENT, $e->getCode(), "Error: " . $e->getMessage(), VidiunBatchJobStatus::FAILED);
		}
	}

	protected function process(VidiunBatchJob $job, VidiunDropFolderContentProcessorJobData $data)
	{
		$job = $this->updateJob($job, "Start processing drop folder files [$data->dropFolderFileIds]", VidiunBatchJobStatus::QUEUED);
		$engine = VDropFolderEngine::getInstance($job->jobSubType);
		$engine->processFolder($job, $data);
		return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED);
	}
		
}

<?php
/**
 * @package Scheduler
 * @subpackage Conversion
 */

/**
 * Will close almost done conversions that sent to remote systems and store the files in the file system.
 * The state machine of the job is as follows:
 * 	 	get almost done conversions 
 * 		check the convert status
 * 		download the converted file
 * 		save recovery file in case of crash
 * 		move the file to the archive
 *
 * @package Scheduler
 * @subpackage Conversion
 */
class VAsyncConvertProfileCloser extends VJobCloserWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::CONVERT_PROFILE;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->checkTimeout($job);
	}

	private function checkTimeout(VidiunBatchJob $job)
	{
		
		if($job->queueTime && ($job->queueTime + self::$taskConfig->params->maxTimeBeforeFail) < time())
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::CLOSER_TIMEOUT, 'Timed out', VidiunBatchJobStatus::FAILED);
		else if ($this->checkConvertDone($job))
		{
			return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED);
		}
			
		return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::ALMOST_DONE);
	}
	
	private function checkConvertDone(VidiunBatchJob $job)
	{
		/**
		 * @var VidiunConvertProfileJobData $data
		 */
		return self::$vClient->batch->checkEntryIsDone($job->id);
	}
}

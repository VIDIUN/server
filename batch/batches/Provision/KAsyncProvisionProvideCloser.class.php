<?php
/**
 * Closes the process of provisioning a new stream.
 *
 * 
 * @package Scheduler
 * @subpackage Provision
 */
class VAsyncProvisionProvideCloser extends VJobCloserWorker
{
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job) {
		$this->closeProvisionProvide($job);
		
	}

	public static function getType()
	{
		return VidiunBatchJobType::PROVISION_PROVIDE;
	}

	protected function closeProvisionProvide (VidiunBatchJob $job)
	{
		if(($job->queueTime + self::$taskConfig->params->maxTimeBeforeFail) < time())
			return new VProvisionEngineResult(VidiunBatchJobStatus::CLOSER_TIMEOUT, "Timed out");
			
		$engine = VProvisionEngine::getInstance( $job->jobSubType, $job->data);
		if ( $engine == null )
		{
			$err = "Cannot find provision engine [{$job->jobSubType}] for job id [{$job->id}]";
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::ENGINE_NOT_FOUND, $err, VidiunBatchJobStatus::FAILED);
		}
		
		VidiunLog::info( "Using engine: " . $engine->getName() );
	
		$results = $engine->checkProvisionedStream($job, $job->data);

		if($results->status == VidiunBatchJobStatus::FINISHED)
			return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED, $results->data);
		
		return $this->closeJob($job, null, null, $results->errMessage, VidiunBatchJobStatus::ALMOST_DONE, $results->data);
		
	}
	
}
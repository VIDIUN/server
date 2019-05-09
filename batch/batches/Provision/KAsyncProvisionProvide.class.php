<?php
/**
 * Will provision new live stream.
 *
 * 
 * @package Scheduler
 * @subpackage Provision
 */
class VAsyncProvisionProvide extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::PROVISION_PROVIDE;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->provision($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}
	
	protected function provision(VidiunBatchJob $job, VidiunProvisionJobData $data)
	{
		$job = $this->updateJob($job, null, VidiunBatchJobStatus::QUEUED);
		
		$engine = VProvisionEngine::getInstance( $job->jobSubType , $data);
		
		if ( $engine == null )
		{
			$err = "Cannot find provision engine [{$job->jobSubType}] for job id [{$job->id}]";
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::ENGINE_NOT_FOUND, $err, VidiunBatchJobStatus::FAILED);
		}
		
		VidiunLog::info( "Using engine: " . $engine->getName() );
	
		$results = $engine->provide($job, $data);

		if($results->status == VidiunBatchJobStatus::FINISHED)
			return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::ALMOST_DONE, $results->data);
			
		return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, null, $results->errMessage, $results->status, $results->data);
	}
}

<?php
/**
 * Will provision new live stram.
 *
 * 
 * @package Scheduler
 * @subpackage Provision
 */
class VAsyncProvisionDelete extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::PROVISION_DELETE;
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
	
		$results = $engine->delete($job, $data);

		if($results->status == VidiunBatchJobStatus::FINISHED)
			return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED, $results->data);
			
		return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, null, $results->errMessage, $results->status, $results->data);
	}
}

<?php
/**
 * Closes asynchronous distribution jobs
 *
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
abstract class VAsyncDistributeCloser extends VJobCloserWorker
{
	/**
	 * @var IDistributionEngine
	 */
	protected $engine;
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->distribute($job, $job->data);
	}
	
	/**
	 * @return DistributionEngine
	 */
	abstract protected function getDistributionEngine($providerType, VidiunDistributionJobData $data);
	
	/**
	 * Throw detailed exceptions for any failure 
	 * @return bool true if job is closed, false for almost done
	 */
	abstract protected function execute(VidiunDistributionJobData $data);
	
	protected function distribute(VidiunBatchJob $job, VidiunDistributionJobData $data)
	{
		
		if(($job->queueTime + self::$taskConfig->params->maxTimeBeforeFail) < time())
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::CLOSER_TIMEOUT, 'Timed out', VidiunBatchJobStatus::FAILED);
		
		try
		{
			$this->engine = $this->getDistributionEngine($job->jobSubType, $data);
			if (!$this->engine)
			{
				VidiunLog::err('Cannot create DistributeEngine of type ['.$job->jobSubType.']');
				$this->closeJob($job, VidiunBatchJobErrorTypes::APP, null, 'Error: Cannot create DistributeEngine of type ['.$job->jobSubType.']', VidiunBatchJobStatus::FAILED);
				return $job;
			}
			$job = $this->updateJob($job, "Engine found [" . get_class($this->engine) . "]", VidiunBatchJobStatus::QUEUED);
						
			$closed = $this->execute($data);
			if($closed)
				return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED, $data);
			 			
			return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::ALMOST_DONE, $data);
		}
		catch(VidiunDistributionException $ex)
		{
			$job = $this->closeJob($job, VidiunBatchJobErrorTypes::APP, $ex->getCode(), "Error: " . $ex->getMessage(), VidiunBatchJobStatus::RETRY, $job->data);
		}
		catch(Exception $ex)
		{
			$job = $this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), VidiunBatchJobStatus::FAILED, $job->data);
		}
		return $job;
	}
}

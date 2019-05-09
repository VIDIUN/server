<?php
/**
 * Base class for all job closer workers.
 * 
 * @package Scheduler
 */
abstract class VJobCloserWorker extends VJobHandlerWorker
{
	public function run($jobs = null)
	{
		if(VBatchBase::$taskConfig->isInitOnly())
			return $this->init();
		
		if(is_null($jobs))
			$jobs = VBatchBase::$vClient->batch->getExclusiveAlmostDone($this->getExclusiveLockKey(), VBatchBase::$taskConfig->maximumExecutionTime, $this->getMaxJobsEachRun(), $this->getFilter(), static::getType());
		
		VidiunLog::info(count($jobs) . " jobs to close");
		
		if(! count($jobs) > 0)
		{
			VidiunLog::info("Queue size: 0 sent to scheduler");
			$this->saveSchedulerQueue(static::getType(), 0);
			return null;
		}
		
		foreach($jobs as &$job)
		{
			try
			{
				self::setCurrentJob($job);
				$job = $this->exec($job);
			}
			catch(VidiunException $kex)
			{
				VBatchBase::unimpersonate();
				$job = $this->closeJob($job, VidiunBatchJobErrorTypes::VIDIUN_API, $kex->getCode(), "Error: " . $kex->getMessage(), VidiunBatchJobStatus::FAILED);
			}
			catch(VidiunClientException $vcex)
			{
				VBatchBase::unimpersonate();
				$job = $this->closeJob($job, VidiunBatchJobErrorTypes::VIDIUN_CLIENT, $vcex->getCode(), "Error: " . $vcex->getMessage(), VidiunBatchJobStatus::RETRY);
			}
			catch(Exception $ex)
			{
				VBatchBase::unimpersonate();
				$job = $this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), VidiunBatchJobStatus::FAILED);
			}
			self::unsetCurrentJob();
		}
			
		return $jobs;
	}
	
	/**
	* @param string $jobType
	* @param boolean $isCloser
	* @return VidiunWorkerQueueFilter
	*/
	protected function getQueueFilter($jobType)
	{
		$workerQueueFilter = $this->getBaseQueueFilter($jobType);
		$workerQueueFilter->filter->statusEqual = VidiunBatchJobStatus::ALMOST_DONE;
		
		return $workerQueueFilter;
	}
}

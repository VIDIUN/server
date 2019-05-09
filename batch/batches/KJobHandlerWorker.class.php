<?php
/**
 * Base class for all job handler workers.
 * 
 * @package Scheduler
 */
abstract class VJobHandlerWorker extends VBatchBase
{
	/**
	 * The job object that currently handled
	 * @var VidiunBatchJob
	 */
	private static $currentJob;
	
	/**
	 * @param VidiunBatchJob $job
	 * @return VidiunBatchJob
	 */
	abstract protected function exec(VidiunBatchJob $job);

	/**
	 * Returns the job object that currently handled
	 * @return VidiunBatchJob
	 */
	public static function getCurrentJob()
	{
		return self::$currentJob;
	}

	/**
	 * @param VidiunBatchJob $currentJob
	 */
	protected static function setCurrentJob(VidiunBatchJob $currentJob)
	{
		VidiunLog::debug("Start job[$currentJob->id] type[$currentJob->jobType] sub-type[$currentJob->jobSubType] object[$currentJob->jobObjectType] object-id[$currentJob->jobObjectId] partner-id[$currentJob->partnerId] dc[$currentJob->dc] parent-id[$currentJob->parentJobId] root-id[$currentJob->rootJobId]");
		self::$currentJob = $currentJob;
		
		self::$vClient->setClientTag(self::$clientTag . " partnerId: " . $currentJob->partnerId);
	}

	protected static function unsetCurrentJob()
	{
		$currentJob = self::getCurrentJob();
		VidiunLog::debug("End job[$currentJob->id]");
		self::$currentJob = null;

		self::$vClient->setClientTag(self::$clientTag);
	}
	
	protected function init()
	{
		$this->saveQueueFilter(static::getType());
	}
	
	protected function getMaxJobsEachRun()
	{
		if(!VBatchBase::$taskConfig->maxJobsEachRun)
			return 1;
		
		return VBatchBase::$taskConfig->maxJobsEachRun;
	}
	
	protected function getJobs()
	{
		$maxJobToPull = VBatchBase::$taskConfig->maxJobToPullToCache;
		return VBatchBase::$vClient->batch->getExclusiveJobs($this->getExclusiveLockKey(), VBatchBase::$taskConfig->maximumExecutionTime, 
				$this->getMaxJobsEachRun(), $this->getFilter(), static::getType(), $maxJobToPull);
	}
	
	public function run($jobs = null)
	{
		if(VBatchBase::$taskConfig->isInitOnly())
			return $this->init();
		
		if(is_null($jobs))
		{
			try
			{
				$jobs = $this->getJobs();
			}
			catch (Exception $e)
			{
				VidiunLog::err($e->getMessage());
				return null;
			}
		}
		
		VidiunLog::info(count($jobs) . " jobs to handle");
		
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
				self::unimpersonate();
			}
			catch(VidiunException $kex)
			{
				self::unimpersonate();
				$this->closeJobOnError($job,VidiunBatchJobErrorTypes::VIDIUN_API, $kex, VidiunBatchJobStatus::FAILED);
			}
			catch(vApplicativeException $kaex)
			{
				self::unimpersonate();
				$this->closeJobOnError($job,VidiunBatchJobErrorTypes::APP, $kaex, VidiunBatchJobStatus::FAILED);
			}
			catch(vTemporaryException $vtex)
			{
				self::unimpersonate();
				if($vtex->getResetJobExecutionAttempts())
					VBatchBase::$vClient->batch->resetJobExecutionAttempts($job->id, $this->getExclusiveLockKey(), $job->jobType);
				
				$this->closeJobOnError($job,VidiunBatchJobErrorTypes::RUNTIME, $vtex, VidiunBatchJobStatus::RETRY, $vtex->getData());
			}
			catch(VidiunClientException $vcex)
			{
				self::unimpersonate();
				$this->closeJobOnError($job,VidiunBatchJobErrorTypes::VIDIUN_CLIENT, $vcex, VidiunBatchJobStatus::RETRY);
			}
			catch(Exception $ex)
			{
				self::unimpersonate();
				$this->closeJobOnError($job,VidiunBatchJobErrorTypes::RUNTIME, $ex, VidiunBatchJobStatus::FAILED);
			}
			self::unsetCurrentJob();
		}
			
		return $jobs;
	}
	
	protected function closeJobOnError($job, $error, $ex, $status, $data = null)
	{
		try
		{
			self::unimpersonate();
			$job = $this->closeJob($job, $error, $ex->getCode(), "Error: " . $ex->getMessage(), $status, $data);
		} 
		catch(Exception $ex)
		{
			VidiunLog::err("Failed to close job after expirencing an error.");
			VidiunLog::err($ex->getMessage());
		}
	}
	
	/**
	 * @param int $jobId
	 * @param VidiunBatchJob $job
	 * @return VidiunBatchJob
	 */
	protected function updateExclusiveJob($jobId, VidiunBatchJob $job)
	{
		return VBatchBase::$vClient->batch->updateExclusiveJob($jobId, $this->getExclusiveLockKey(), $job);
	}
	
	/**
	 * @param VidiunBatchJob $job
	 * @return VidiunBatchJob
	 */
	protected function freeExclusiveJob(VidiunBatchJob $job)
	{
		$resetExecutionAttempts = false;
		if ($job->status == VidiunBatchJobStatus::ALMOST_DONE)
			$resetExecutionAttempts = true;
		
		$response = VBatchBase::$vClient->batch->freeExclusiveJob($job->id, $this->getExclusiveLockKey(), static::getType(), $resetExecutionAttempts);
		
		if(is_numeric($response->queueSize)) {
			VidiunLog::info("Queue size: $response->queueSize sent to scheduler");
			$this->saveSchedulerQueue(static::getType(), $response->queueSize);
		}
		
		return $response->job;
	}
	
	/**
	 * @return VidiunBatchJobFilter
	 */
	protected function getFilter()
	{
		$filter = new VidiunBatchJobFilter();
		if(VBatchBase::$taskConfig->filter)
			$filter = VBatchBase::$taskConfig->filter;
		
		if (VBatchBase::$taskConfig->minCreatedAtMinutes && is_numeric(VBatchBase::$taskConfig->minCreatedAtMinutes))
		{
			$minCreatedAt = time() - (VBatchBase::$taskConfig->minCreatedAtMinutes * 60);
			$filter->createdAtLessThanOrEqual = $minCreatedAt;
		}
		
		return $filter;
	}
	
	/**
	 * @return VidiunExclusiveLockKey
	 */
	protected function getExclusiveLockKey()
	{
		$lockKey = new VidiunExclusiveLockKey();
		$lockKey->schedulerId = $this->getSchedulerId();
		$lockKey->workerId = $this->getId();
		$lockKey->batchIndex = $this->getIndex();
		
		return $lockKey;
	}
	
	/**
	 * @param VidiunBatchJob $job
	 */
	protected function onFree(VidiunBatchJob $job)
	{
		$this->onJobEvent($job, VBatchEvent::EVENT_JOB_FREE);
	}
	
	/**
	 * @param VidiunBatchJob $job
	 */
	protected function onUpdate(VidiunBatchJob $job)
	{
		$this->onJobEvent($job, VBatchEvent::EVENT_JOB_UPDATE);
	}
	
	/**
	 * @param VidiunBatchJob $job
	 * @param int $event_id
	 */
	protected function onJobEvent(VidiunBatchJob $job, $event_id)
	{
		$event = new VBatchEvent();
		
		$event->partner_id = $job->partnerId;
		$event->entry_id = $job->entryId;
		$event->bulk_upload_id = $job->bulkJobId;
		$event->batch_parant_id = $job->parentJobId;
		$event->batch_root_id = $job->rootJobId;
		$event->batch_status = $job->status;
		
		$this->onEvent($event_id, $event);
	}
	
	/**
	 * @param string $jobType
	 * @return VidiunWorkerQueueFilter
	 */
	protected function getBaseQueueFilter($jobType)
	{
		$filter = $this->getFilter();
		$filter->jobTypeEqual = $jobType;
		
		$workerQueueFilter = new VidiunWorkerQueueFilter();
		$workerQueueFilter->schedulerId = $this->getSchedulerId();
		$workerQueueFilter->workerId = $this->getId();
		$workerQueueFilter->filter = $filter;
		$workerQueueFilter->jobType = $jobType;
		
		return $workerQueueFilter;
	}
	
	/**
	 * @param string $jobType
	 * @param boolean $isCloser
	 * @return VidiunWorkerQueueFilter
	 */
	protected function getQueueFilter($jobType)
	{
		$workerQueueFilter = $this->getBaseQueueFilter($jobType);
		//$workerQueueFilter->filter->statusIn = VidiunBatchJobStatus::PENDING . ',' . VidiunBatchJobStatus::RETRY;
		
		return $workerQueueFilter;
	}
	
	/**
	 * @param int $jobType
	 */
	protected function saveQueueFilter($jobType)
	{
		$filter = $this->getQueueFilter($jobType);
		
		$type = VBatchBase::$taskConfig->name;
		$file = "$type.flt";
		
		VScheduleHelperManager::saveFilter($file, $filter);
	}
	
	/**
	 * @param int $jobType
	 * @param int $size
	 */
	protected function saveSchedulerQueue($jobType, $size = null)
	{
		if(is_null($size))
		{
			$workerQueueFilter = $this->getQueueFilter($jobType);
			$size = VBatchBase::$vClient->batch->getQueueSize($workerQueueFilter);
		}
		
		$queueStatus = new VidiunBatchQueuesStatus();
		$queueStatus->workerId = $this->getId();
		$queueStatus->jobType = $jobType;
		$queueStatus->size = $size;
		
		$this->saveSchedulerCommands(array($queueStatus));
	}
	
	/**
	 * @return VidiunBatchJob
	 */
	protected function newEmptyJob()
	{
		return new VidiunBatchJob();
	}
	
	/**
	 * @param VidiunBatchJob $job
	 * @param string $msg
	 * @param int $status
	 * @param unknown_type $data
	 * @param boolean $remote
	 * @return VidiunBatchJob
	 */
	protected function updateJob(VidiunBatchJob $job, $msg, $status, VidiunJobData $data = null)
	{
		$updateJob = $this->newEmptyJob();
		
		if(! is_null($msg))
		{
			$updateJob->message = $msg;
			$updateJob->description = $job->description . "\n$msg";
		}
		
		$updateJob->status = $status;
		$updateJob->data = $data;
		
		VidiunLog::info("job[$job->id] status: [$status] msg : [$msg]");
		if($this->isUnitTest)
			return $job;
		
		$job = $this->updateExclusiveJob($job->id, $updateJob);
		if($job instanceof VidiunBatchJob)
			$this->onUpdate($job);
		
		return $job;
	}
	
	/**
	 * @param VidiunBatchJob $job
	 * @param int $errType
	 * @param int $errNumber
	 * @param string $msg
	 * @param int $status
	 * @param VidiunJobData $data
	 * @return VidiunBatchJob
	 */
	protected function closeJob(VidiunBatchJob $job, $errType, $errNumber, $msg, $status, $data = null)
	{
		if(! is_null($errType))
			VidiunLog::err($msg);
		
		$updateJob = $this->newEmptyJob();
		
		if(! is_null($msg))
		{
			$updateJob->message = $msg;
			$updateJob->description = $job->description . "\n$msg";
		}
		
		$updateJob->status = $status;
		$updateJob->errType = $errType;
		$updateJob->errNumber = $errNumber;
		$updateJob->data = $data;
		
		VidiunLog::info("job[$job->id] status: [$status] msg : [$msg]");
		if($this->isUnitTest)
		{
			$job->status = $updateJob->status;
			$job->message = $updateJob->message;
			$job->description = $updateJob->description;
			$job->errType = $updateJob->errType;
			$job->errNumber = $updateJob->errNumber;
			return $job;
		}
		
		$job = $this->updateExclusiveJob($job->id, $updateJob);
		if($job instanceof VidiunBatchJob)
			$this->onUpdate($job);
		
		VidiunLog::info("Free job[$job->id]");
		$job = $this->freeExclusiveJob($job);
		if($job instanceof VidiunBatchJob)
			$this->onFree($job);
		
		return $job;		
	}
}

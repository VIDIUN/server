<?php
/**
 * @package Scheduler
 * @subpackage Bulk-Upload
 */

setlocale ( LC_ALL, 'en_US.UTF-8' );

/**
 * Will initiate a single bulk upload.
 * The state machine of the job is as follows:
 * get the csv, parse it and validate it
 * creates the entries
 *
 * @package Scheduler
 * @subpackage Bulk-Upload
 */
class VAsyncBulkUpload extends VJobHandlerWorker 
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::BULKUPLOAD;
	}

	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		ini_set('auto_detect_line_endings', true);
		try
		{
			$job = $this->startBulkUpload($job);
		}
		catch (VidiunBulkUploadAbortedException $abortedException)
		{
			self::unimpersonate();
			$job = $this->closeJob($job, null, null, null, VidiunBatchJobStatus::ABORTED);
		}
		catch(VidiunBatchException $kbex)
		{
			self::unimpersonate();
			$job = $this->closeJob($job, VidiunBatchJobErrorTypes::APP, $kbex->getCode(), "Error: " . $kbex->getMessage(), VidiunBatchJobStatus::FAILED);
		}
		catch(VidiunException $kex)
		{
			self::unimpersonate();
			$job = $this->closeJob($job, VidiunBatchJobErrorTypes::VIDIUN_API, $kex->getCode(), "Error: " . $kex->getMessage(), VidiunBatchJobStatus::FAILED);
		}
		catch(VidiunClientException $vcex)
		{
			self::unimpersonate();
			$job = $this->closeJob($job, VidiunBatchJobErrorTypes::VIDIUN_CLIENT, $vcex->getCode(), "Error: " . $vcex->getMessage(), VidiunBatchJobStatus::RETRY);
		}
		catch(Exception $ex)
		{
			self::unimpersonate();
			$job = $this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), VidiunBatchJobStatus::FAILED);
		}
		ini_set('auto_detect_line_endings', false);

		return $job;
	}


	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}

	/**
	 * Starts the bulk upload
	 * @param VidiunBatchJob $job
	 * @return VidiunBatchJob
	 * @throws VidiunBatchException
	 * @throws VidiunException
	 */
	private function startBulkUpload(VidiunBatchJob $job)
	{
		VidiunLog::info( "Start bulk upload ($job->id)" );
		
		//Gets the right Engine instance 
		$engine = VBulkUploadEngine::getEngine($job->jobSubType, $job);
		if (is_null ( $engine )) {
			throw new VidiunException ( "Unable to find bulk upload engine", VidiunBatchJobAppErrors::ENGINE_NOT_FOUND );
		}
		$job = $this->updateJob($job, 'Parsing file [' . $engine->getName() . ']', VidiunBatchJobStatus::QUEUED, $engine->getData());
		$this->appendPrivilegesToVs($job->data->privileges);
		$engine->setJob($job);
		$engine->setData($job->data);
		$engine->handleBulkUpload();
		
		$job = $engine->getJob();
		$data = $engine->getData();

		$countObjects = $this->countCreatedObjects($job->id, $job->data->bulkUploadObjectType);
		$countHandledObjects = $countObjects[0];
		$countErrorObjects = $countObjects[1];

		if(!$countHandledObjects && !$engine->shouldRetry() && $countErrorObjects)
			throw new VidiunBatchException("None of the uploaded items were processed succsessfuly", VidiunBatchJobAppErrors::BULK_NO_ENTRIES_HANDLED, $engine->getData());
		
		if($engine->shouldRetry())
		{
			self::$vClient->batch->resetJobExecutionAttempts($job->id, $this->getExclusiveLockKey(), $job->jobType);
			return $this->closeJob($job, null, null, "Retrying: ".$countHandledObjects." ".$engine->getObjectTypeTitle()." objects were handled until now", VidiunBatchJobStatus::RETRY);
		}

		//check if all items were done already
		if(!self::$vClient->batch->updateBulkUploadResults($job->id) && !$countErrorObjects)
		{
			return $this->closeJob($job, null, null, 'Finished successfully', VidiunBatchJobStatus::FINISHED);
		}
			
		return $this->closeJob($job, null, null, 'Waiting for objects closure', VidiunBatchJobStatus::ALMOST_DONE, $data);
	}
	
	/**
	 * Return the count of created entries
	 * @param int $jobId
	 * @return int
	 */
	protected function countCreatedObjects($jobId, $bulkuploadObjectType) 
	{
		$createdCount = 0;
		$errorCount = 0;
		
		$counters = self::$vClient->batch->countBulkUploadEntries($jobId, $bulkuploadObjectType);
		foreach($counters as $counter)
		{
			/** @var VidiunKeyValue $counter */
			if ($counter->key == 'created')
				$createdCount = $counter->value;
			if ($counter->key == 'error')
				$errorCount = $counter->value;
		}
		
		return array($createdCount, $errorCount);
	}
	
}

<?php
/**
 * @package Scheduler
 * @subpackage Bulk-Upload
 */

/**
 * Will close almost done bulk uploads.
 * The state machine of the job is as follows:
 * 	 	get almost done bulk uploads 
 * 		check the imports and converts statuses
 * 		update the bulk status
 *
 * @package Scheduler
 * @subpackage Bulk-Upload
 */
class VAsyncBulkUploadCloser extends VJobCloserWorker
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
		return $this->fetchStatus($job);
	}
	
	private function fetchStatus(VidiunBatchJob $job)
	{
		if(($job->queueTime + self::$taskConfig->params->maxTimeBeforeFail) < time())
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::CLOSER_TIMEOUT, 'Timed out', VidiunBatchJobStatus::FAILED);
			
		$openedEntries = self::$vClient->batch->updateBulkUploadResults($job->id);
		$job = $this->updateJob($job, "Unclosed entries remaining: $openedEntries" , VidiunBatchJobStatus::ALMOST_DONE);
		if(!$openedEntries)
		{
		    $numOfObjects = $job->data->numOfObjects;
		    $numOfErrorObjects = $job->data->numOfErrorObjects;
		    VidiunLog::info("numOfSuccessObjects: $numOfObjects, numOfErrorObjects: $numOfErrorObjects");
		    
		    if ($numOfErrorObjects == 0)
		    {
			    return $this->closeJob($job, null, null, 'Finished successfully', VidiunBatchJobStatus::FINISHED);
		    }
		    else if($numOfObjects > 0) //some objects created successfully
		    {
		    	return $this->closeJob($job, null, null, 'Finished, but with some errors', VidiunBatchJobStatus::FINISHED_PARTIALLY);
		    }
		    else
		    {
		        return $this->closeJob($job, null, null, 'Failed to create objects', VidiunBatchJobStatus::FAILED);
		    }
		}	
		return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::ALMOST_DONE);
	}
}

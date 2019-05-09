<?php
/**
 * Will transform metadata XML based on XSL and will update the metadata object with the new version 
 *
 * @package plugins.metadata
 * @subpackage Scheduler.Transform
 */
class VAsyncTransformMetadata extends VJobHandlerWorker
{
	/**
	 * @var int
	 */
	protected $multiRequestSize = 20;
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::METADATA_TRANSFORM;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->upgrade($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getJobs()
	 * 
	 * TODO remove the destXsdPath from the job data and get it later using the api, then delete this method
	 */
	protected function getJobs()
	{
		$maxJobToPull = VBatchBase::$taskConfig->maxJobToPullToCache;
		return self::$vClient->metadataBatch->getExclusiveTransformMetadataJobs($this->getExclusiveLockKey(), self::$taskConfig->maximumExecutionTime, 1, 
				$this->getFilter(), $maxJobToPull);
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}
	
	private function invalidateFailedMetadatas($results, $transformObjectIds = array())
	{
		self::$vClient->startMultiRequest();
		foreach($results as $index => $result){
        	if(is_array($result) && isset($result['code']) && isset($result['message'])){
              	VidiunLog::err('error in object id['.$transformObjectIds[$index] .'] with code: '. $result['code']."\n".$result['message']." going to invalidate it");
              	self::$vClient->metadata->invalidate($transformObjectIds[$index]);
        	}
        }
        $resultsOfInvalidating = self::$vClient->doMultiRequest();
		if (!$resultsOfInvalidating)
			return;
		foreach($resultsOfInvalidating as $index => $resultOfInvalidating){
        	if(is_array($resultOfInvalidating) && isset($resultOfInvalidating['code']) && isset($resultOfInvalidating['message'])){
              	VidiunLog::err('error while invalidating object id['.$transformObjectIds[$index] .'] with code: '. $resultOfInvalidating['code']."\n".$resultOfInvalidating['message']);        	
        	}
        }	
	}
	
	private function upgrade(VidiunBatchJob $job, VidiunTransformMetadataJobData $data)
	{
		if(self::$taskConfig->params->multiRequestSize)
			$this->multiRequestSize = self::$taskConfig->params->multiRequestSize;
		
		$pager = new VidiunFilterPager();
		$pager->pageSize = 40;
		if(self::$taskConfig->params && self::$taskConfig->params->maxObjectsEachRun)
			$pager->pageSize = self::$taskConfig->params->maxObjectsEachRun;
		
		$transformList = self::$vClient->metadataBatch->getTransformMetadataObjects(
			$data->metadataProfileId,
			$data->srcVersion,
			$data->destVersion,
			$pager
		);
			
		if(!$transformList->totalCount) // if no metadata objects returned
		{
			if(!$transformList->lowerVersionCount) // if no metadata objects of lower version exist
			{
				$this->closeJob($job, null, null, 'All metadata transformed', VidiunBatchJobStatus::FINISHED);
				return $job;
			}
			
			$this->closeJob($job, null, null, "Waiting for metadata objects [$transformList->lowerVersionCount] of lower versions", VidiunBatchJobStatus::RETRY);
			return $job;
		}
		
		if($transformList->lowerVersionCount || $transformList->totalCount) // another retry will be needed later
		{
			self::$vClient->batch->resetJobExecutionAttempts($job->id, $this->getExclusiveLockKey(), $job->jobType);
		}
			
		self::$vClient->startMultiRequest();
		$transformObjectIds = array();
		foreach($transformList->objects as $object)
		{
			/* @var $object VidiunMetadata */
			$xslStr = vEncryptFileUtils::getEncryptedFileContent($data->srcXsl->filePath, $data->srcXsl->encryptionKey, self::getIV());
			$xml = vXsd::transformXmlData($object->xml, $data->destXsd, $xslStr);
			if($xml)
			{
				self::$vClient->metadata->update($object->id, $xml, $object->version);
			}
			else 
			{			
				self::$vClient->metadata->invalidate($object->id, $object->version);
			}
			
			$transformObjectIds[] = $object->id;
				    
			if(self::$vClient->getMultiRequestQueueSize() >= $this->multiRequestSize)
			{
				$results = self::$vClient->doMultiRequest();
				$this->invalidateFailedMetadatas($results, $transformObjectIds);
				$transformObjectIds = array();
				self::$vClient->startMultiRequest();
			}
			
		}
		$results = self::$vClient->doMultiRequest();
		$this->invalidateFailedMetadatas($results, $transformObjectIds);
		
		$this->closeJob($job, null, null, "Metadata objects [" . count($transformList->objects) . "] transformed", VidiunBatchJobStatus::RETRY);
		
		return $job;
	}
}

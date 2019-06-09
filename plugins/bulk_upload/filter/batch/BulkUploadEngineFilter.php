<?php
/**
 * Class which parses the bulk upload Filter and creates the objects listed in it.
 *
 * @package plugins.bulkUploadFilter
 * @subpackage batch
 */
abstract class BulkUploadEngineFilter extends VBulkUploadEngine
{
	/**
	 * The bulk upload results
	 * @var array
	 */
	protected $bulkUploadResults = array();
	
	protected $handledObjectsCount;
	
	protected $startIndex;
			

	/* (non-PHPdoc)
	 * @see VBulkUploadEngine::handleBulkUpload()
	 */
	public function handleBulkUpload()
	{
		$this->startIndex = $this->getStartIndex($this->job->id);
		
		$this->processObjectsList();
		
		// send all invalid results
		VBatchBase::$vClient->doMultiRequest();
		
		VidiunLog::info("Extracted objects by filter, $this->handledObjectsCount lines with " . ($this->handledObjectsCount - count($this->bulkUploadResults)) . ' invalid records');
				
		//Check if job aborted
		$this->checkAborted();

		//Create the objects from the bulk upload results
		$this->createObjects();
	}
		
	/* (non-PHPdoc)
	 * @see VBulkUploadEngine::addBulkUploadResult()
	 */
	protected function addBulkUploadResult(VidiunBulkUploadResult $bulkUploadResult)
	{
		parent::addBulkUploadResult($bulkUploadResult);
			
	}
	
	abstract protected function listObjects(VidiunFilter $filter, VidiunFilterPager $pager = null); 
	
	abstract protected function createObjectFromResultAndJobData (VidiunBulkUploadResult $bulkUploadResult);

	abstract protected function deleteObjectFromResult (VidiunBulkUploadResult $bulkUploadResult);
	
	abstract protected function fillUploadResultInstance ($object);
	
	abstract protected function getBulkUploadResultObjectType ();
	
	protected function isErrorResult($requestResult){
		if(is_array($requestResult) && isset($requestResult['code'])){
			return true;
		}
		if($requestResult instanceof Exception){
			return true;
		}
		return false;
	}
	
	/**
	 *
	 * Creates a new upload result object from the given parameters
	 * @param VidiunObject $object
	 * @return VidiunBulkUploadResult
	 */
	protected function createUploadResult($object)
	{
	    if($this->handledRecordsThisRun > $this->maxRecordsEachRun)
		{
			$this->exceededMaxRecordsEachRun = true;
			return null;
		}
		$this->handledRecordsThisRun++;
		
	    $bulkUploadResult = $this->fillUploadResultInstance($object);
		$bulkUploadResult->bulkUploadJobId = $this->job->id;
		$bulkUploadResult->lineIndex = $this->startIndex + $this->handledObjectsCount;
		$bulkUploadResult->partnerId = $this->job->partnerId;
		$bulkUploadResult->status = VidiunBulkUploadResultStatus::IN_PROGRESS;
		if (!$bulkUploadResult->action)
		{
		    $bulkUploadResult->action = VidiunBulkUploadAction::ADD;
		}	
		$bulkUploadResult->bulkUploadResultObjectType = $this->getBulkUploadResultObjectType(); 
			
		$this->bulkUploadResults[] = $bulkUploadResult;
		
		return $bulkUploadResult;
	}
	
	/**
	 * Get objects according to the input filter and create bulkUploadResults for each one of them
	 * 
	 */
	protected function processObjectsList()
	{
		VBatchBase::impersonate($this->currentPartnerId);
		$pager = new VidiunFilterPager();
		$pager->pageSize = 100;		
		if(VBatchBase::$taskConfig->params->pageSize)
			$pager->pageSize = VBatchBase::$taskConfig->params->pageSize;			
		$pager->pageIndex = $this->getPagerIndex($pager->pageSize);

		$list = $this->listObjects($this->getData()->filter, $pager);
		$stop = false;
		
		while(count($list->objects) && !$stop)
		{
			foreach ($list->objects as $object) 
			{
				$this->handledObjectsCount ++;
					
				// creates a result object
				$this->createUploadResult($object);
				if($this->exceededMaxRecordsEachRun)
				{
					VBatchBase::unimpersonate();
					return;
				}
				
				if(VBatchBase::$vClient->getMultiRequestQueueSize() >= $this->multiRequestSize)
				{
					VBatchBase::$vClient->doMultiRequest();
					$this->checkAborted();
					VBatchBase::$vClient->startMultiRequest();
				}	
			}
			if(count($list->objects) < $pager->pageSize)
				$stop = true;
			else 
			{
				$pager->pageIndex = $this->getPagerIndex($pager->pageSize);						
				$list = $this->listObjects($this->getData()->filter, $pager);
			}
		}
		
		VBatchBase::unimpersonate();
	}

	/**
	 * 
	 * Create the objects from the given bulk upload results
	 */
	protected function createObjects()
	{
		VidiunLog::info("job[{$this->job->id}] start creating objects");
		
		$bulkUploadResultChunk = array(); // store the results of the created entries
				
		VBatchBase::impersonate($this->currentPartnerId);;
		VBatchBase::$vClient->startMultiRequest();
		
		foreach($this->bulkUploadResults as $bulkUploadResult)
		{
			/* @var $bulkUploadResult VidiunBulkUploadResultCategoryEntry */
		    switch ($bulkUploadResult->action)
		    {
		        case VidiunBulkUploadAction::ADD:
    		        $this->createObjectFromResultAndJobData($bulkUploadResult);
        			$bulkUploadResultChunk[] = $bulkUploadResult;
		            break;
		        		            
		        case VidiunBulkUploadAction::DELETE:
		            $bulkUploadResultChunk[] = $bulkUploadResult;
        			$this->deleteObjectFromResult($bulkUploadResult);      			
		            break;
		        
		        default:
		            $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
		            $bulkUploadResult->errorDescription = "Unsupported action passed: [".$bulkUploadResult->action ."]";
		            break;
		    }
		    
		    if(VBatchBase::$vClient->getMultiRequestQueueSize() >= $this->multiRequestSize)
			{
				$requestResults = VBatchBase::$vClient->doMultiRequest();
				VBatchBase::unimpersonate();
				$this->updateObjectsResults($requestResults, $bulkUploadResultChunk);
				$this->checkAborted();
				VBatchBase::impersonate($this->currentPartnerId);;
				VBatchBase::$vClient->startMultiRequest();
				$bulkUploadResultChunk = array();
			}
		}
		
		// make all the category actions as the partner
		$requestResults = VBatchBase::$vClient->doMultiRequest();
		
		VBatchBase::unimpersonate();
		
		if($requestResults && count($requestResults))
			$this->updateObjectsResults($requestResults, $bulkUploadResultChunk);
		
		VidiunLog::info("job[{$this->job->id}] finished creating objects");
	}
	
    protected function updateObjectsResults(array $requestResults, array $bulkUploadResults)
	{
	    VBatchBase::$vClient->startMultiRequest();
		VidiunLog::info("Updating " . count($requestResults) . " results");
		
		foreach($requestResults as $index => $requestResult)
		{
			$bulkUploadResult = $bulkUploadResults[$index];
			
			if(is_array($requestResult) && isset($requestResult['code']))
			{
				if($this->isErrorResult($requestResult)){
				    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
				    $bulkUploadResult->errorType = VidiunBatchJobErrorTypes::VIDIUN_API;
					$bulkUploadResult->objectStatus = $requestResult['code'];
					$bulkUploadResult->errorDescription = $requestResult['message'];
					$this->addBulkUploadResult($bulkUploadResult);	
					continue;				
				}				
			}
			
			if($requestResult instanceof Exception)
			{
				if($this->isErrorResult($requestResult)){
					$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
					$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::VIDIUN_API;
					$bulkUploadResult->errorDescription = $requestResult->getMessage();
					$this->addBulkUploadResult($bulkUploadResult);
					continue;
				}				
			}
			
			// update the results with the new object Id
			if (isset($requestResult->id) && $requestResult->id && !$bulkUploadResult->objectId)
			    $bulkUploadResult->objectId = $requestResult->id;
			$this->addBulkUploadResult($bulkUploadResult);
		}
		
		VBatchBase::$vClient->doMultiRequest();
	}
	
	private function getPagerIndex($pageSize)
	{	
		return (int)(($this->startIndex + $this->handledObjectsCount) / $pageSize) + 1;
	}
}

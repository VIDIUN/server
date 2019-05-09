<?php
/**
 * Class which parses the bulk upload iCal and creates the objects defined in it.
 *
 * @package plugins.scheduleBulkUpload
 * @subpackage batch
 */
class BulkUploadEngineICal extends VBulkUploadEngine
{
    const OBJECT_TYPE_TITLE = 'schedule-event';
    const CHUNK_SIZE = 20;
    const MAX_IN_FILTER = 100;

    /**
     * @var int
     */
    protected $itemIndex = 0;
    
	/**
	 * The bulk upload results
	 * @var array
	 */
	protected $bulkUploadResults = array();
    
	/**
	 * The bulk upload items
	 * @var array<vSchedulingICalEvent>
	 */
	protected $items = array();
    
    protected function createUploadResults()
    {
    	$items = $this->items;
    	
		$this->itemIndex = $this->getStartIndex($this->job->id);
		if($this->itemIndex)
		{
			$items = array_slice($items, $this->itemIndex);
		}
		
		$chunks = array_chunk($items, self::CHUNK_SIZE);
		foreach($chunks as $chunk)
		{
			VBatchBase::$vClient->startMultiRequest();
			foreach($chunk as $item)
			{
				/* @var $item vSchedulingICalEvent */
				$bulkUploadResult = $this->createUploadResult($item);
				if($bulkUploadResult)
				{
					$this->bulkUploadResults[] = $bulkUploadResult;
				}
				else
				{
					break;
				}
			}
			VBatchBase::$vClient->doMultiRequest();
		}
    }
    
    protected function createUploadResult(vSchedulingICalEvent $iCal)
    {
    	if($this->handledRecordsThisRun > $this->maxRecordsEachRun)
    	{
    		$this->exceededMaxRecordsEachRun = true;
    		return null;
    	}
    	$this->handledRecordsThisRun++;
    
    	$bulkUploadResult = new VidiunBulkUploadResultScheduleEvent();
    	$bulkUploadResult->bulkUploadJobId = $this->job->id;
    	$bulkUploadResult->lineIndex = $this->itemIndex;
    	$bulkUploadResult->partnerId = $this->job->partnerId;
    	$bulkUploadResult->referenceId = $iCal->getUid();
    	$bulkUploadResult->bulkUploadResultObjectType = VidiunBulkUploadObjectType::SCHEDULE_EVENT;
    	$bulkUploadResult->rowData = $iCal->getRaw();
		$bulkUploadResult->objectStatus = VidiunScheduleEventStatus::ACTIVE;
		$bulkUploadResult->status = VidiunBulkUploadResultStatus::IN_PROGRESS;

    	if($iCal->getMethod() == vSchedulingICal::METHOD_CANCEL)
    	{
    		$bulkUploadResult->action = VidiunBulkUploadAction::CANCEL;
    	}
    
    	$this->itemIndex++;

    	return $bulkUploadResult;
    }

    protected function updateObjectsResults(array $requestResults, array $bulkUploadResults)
    {
    	VBatchBase::$vClient->startMultiRequest();
    
    	// checking the created entries
    	foreach($requestResults as $index => $requestResult)
    	{
    		$bulkUploadResult = $bulkUploadResults[$index];
    			
    		if(VBatchBase::$vClient->isError($requestResult))
    		{
    			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
    			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::VIDIUN_API;
    			$bulkUploadResult->objectStatus = $requestResult['code'];
    			$bulkUploadResult->errorDescription = $requestResult['message'];
    			$this->addBulkUploadResult($bulkUploadResult);
    			continue;
    		}
    			
    		if($requestResult instanceof Exception)
    		{
    			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
    			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::VIDIUN_API;
    			$bulkUploadResult->errorDescription = $requestResult->getMessage();
    			$this->addBulkUploadResult($bulkUploadResult);
    			continue;
    		}
    			
    		// update the results with the new object Id
    		if ($requestResult->id)
    			$bulkUploadResult->objectId = $requestResult->id;
    			$this->addBulkUploadResult($bulkUploadResult);
    	}
    
    	VBatchBase::$vClient->doMultiRequest();
    }
    
    protected function getExistingEvents()
    {
    	$schedulePlugin = VidiunScheduleClientPlugin::get(VBatchBase::$vClient);

    	$pager = new VidiunFilterPager();
    	$pager->pageSize = self::MAX_IN_FILTER;
    	
		VBatchBase::$vClient->startMultiRequest();
		$referenceIds = array();
		foreach($this->bulkUploadResults as $bulkUploadResult)
		{
			/* @var $bulkUploadResult VidiunBulkUploadResultScheduleEvent */
		    if($bulkUploadResult->action == VidiunBulkUploadAction::CANCEL)
		    	continue;
		    
		    $item = $this->items[$bulkUploadResult->lineIndex];
		    /* @var $item vSchedulingICalEvent */
		    
		    if(!$item->getUid())
		    	continue;
		    
		    $referenceIds[] = $item->getUid();
		    if(count($referenceIds) >= self::MAX_IN_FILTER)
		    {
		    	$filter = new VidiunScheduleEventFilter();
		    	$filter->referenceIdIn = implode(',', $referenceIds);
		    	$schedulePlugin->scheduleEvent->listAction($filter, $pager);
		    }
		}
	    if(count($referenceIds))
	    {
	    	$filter = new VidiunScheduleEventFilter();
	    	$filter->referenceIdIn = implode(',', $referenceIds);
	    	$schedulePlugin->scheduleEvent->listAction($filter, $pager);
	    	$referenceIds = array();
	    }
		$results = VBatchBase::$vClient->doMultiRequest();

		$existingEvents = array();
	    if (is_array($results) || is_object($results))
	    {
		    foreach($results as $result)
		    {
			    VBatchBase::$vClient->throwExceptionIfError($result);
			    /* @var $result VidiunScheduleEventListResponse */
			    foreach($result->objects as $scheduleEvent)
			    {
				    /* @var $scheduleEvent VidiunScheduleEvent */
				    $existingEvents[$scheduleEvent->referenceId] = $scheduleEvent->id;
			    }
		    }
	    }
	    return $existingEvents;
    }
    
    protected function createObjects()
    {
    	$schedulePlugin = VidiunScheduleClientPlugin::get(VBatchBase::$vClient);
		
		$existingEvents = $this->getExistingEvents();

		VBatchBase::$vClient->startMultiRequest();
		
		$bulkUploadResultChunk = array();
		foreach($this->bulkUploadResults as $bulkUploadResult)
		{
		    $item = $this->items[$bulkUploadResult->lineIndex];
		    /* @var $item vSchedulingICalEvent */
		    
			$bulkUploadResultChunk[] = $bulkUploadResult;
			VBatchBase::impersonate($this->currentPartnerId);;
			
			/* @var $bulkUploadResult VidiunBulkUploadResultScheduleEvent */
			if($bulkUploadResult->action == VidiunBulkUploadAction::CANCEL)
			{
				$schedulePlugin->scheduleEvent->cancel($bulkUploadResult->referenceId);
			}
			elseif (isset($existingEvents[$bulkUploadResult->referenceId]))
			{
				$scheduleEventId = $existingEvents[$bulkUploadResult->referenceId];
				$schedulePlugin->scheduleEvent->update($scheduleEventId, $item->toObject());
			}
			else 
			{
				$schedulePlugin->scheduleEvent->add($item->toObject());
			}
			
			VBatchBase::unimpersonate();
		
			if(VBatchBase::$vClient->getMultiRequestQueueSize() >= $this->multiRequestSize)
			{
				// make all the media->add as the partner
				$requestResults = VBatchBase::$vClient->doMultiRequest();
		
				$this->updateObjectsResults($requestResults, $bulkUploadResultChunk);
				$this->checkAborted();
				VBatchBase::$vClient->startMultiRequest();
				$bulkUploadResultChunk = array();
			}
		}
		
		// make all the category actions as the partner
		$requestResults = VBatchBase::$vClient->doMultiRequest();
		
		if(count($requestResults))
			$this->updateObjectsResults($requestResults, $bulkUploadResultChunk);

		VidiunLog::info("job[{$this->job->id}] finish modifying users");
    }
    
	/**
	 * {@inheritDoc}
	 * @see VBulkUploadEngine::handleBulkUpload()
	 */
	public function handleBulkUpload()
	{
		$calendar = vSchedulingICal::parse(file_get_contents($this->data->filePath), $this->data->eventsType);
		$this->items = $calendar->getComponents();
		
		$this->createUploadResults();
		$this->createObjects();
	}

	/**
	 * {@inheritDoc}
	 * @see VBulkUploadEngine::getObjectTypeTitle()
	 */
	public function getObjectTypeTitle()
	{
		return self::OBJECT_TYPE_TITLE;
	}
}

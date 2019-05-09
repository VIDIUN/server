<?php
/**
 * Class which parses the bulk upload CSV and creates the objects listed in it. 
 * This engine class parses CSVs which describe schedule-resource objects.
 * 
 * @package plugins.scheduleBulkUpload
 * @subpackage batch
 */
class BulkUploadScheduleResourceEngineCsv extends BulkUploadEngineCsv
{
	const OBJECT_TYPE_TITLE = 'schedule-resource';
    const MAX_IN_FILTER = 100;
	
    protected static $validTypes = array(
		'location',
    	'camera',
    	'live_entry',
    );
    
    protected $existingSystemNames = array();
    protected $parentSystemNames = array();
    
	/**
	 * (non-PHPdoc)
	 * 
	 * @see BulkUploadGeneralEngineCsv::createUploadResult()
	 */
	protected function createUploadResult($values, $columns)
	{
		$bulkUploadResult = parent::createUploadResult($values, $columns);
		if(!$bulkUploadResult)
			return;
		
		$bulkUploadResult->bulkUploadResultObjectType = VidiunBulkUploadObjectType::SCHEDULE_RESOURCE;
		
		// trim the values
		array_walk($values, array('BulkUploadScheduleResourceEngineCsv', 'trimArray'));
		
		// sets the result values
		foreach($columns as $index => $column)
		{
			if(!is_numeric($index))
				continue;
			
			if(iconv_strlen($values[$index], 'UTF-8'))
			{
				$bulkUploadResult->$column = $values[$index];
			}
		}
		
		$bulkUploadResult->objectStatus = VidiunScheduleResourceStatus::ACTIVE;
		$bulkUploadResult->status = VidiunBulkUploadResultStatus::IN_PROGRESS;
		
		if(!$bulkUploadResult->action)
		{
			$bulkUploadResult->action = VidiunBulkUploadAction::ADD;
		}
		
		$bulkUploadResult = $this->validateBulkUploadResult($bulkUploadResult);
		if($bulkUploadResult)
			$this->bulkUploadResults[] = $bulkUploadResult;
	}
	
	protected function validateBulkUploadResult(VidiunBulkUploadResult $bulkUploadResult)
	{
		/* @var $bulkUploadResult VidiunBulkUploadResultScheduleResource */
		if(!$bulkUploadResult->resourceId && !$bulkUploadResult->systemName && ($bulkUploadResult->action == VidiunBulkUploadAction::UPDATE || $bulkUploadResult->action == VidiunBulkUploadAction::DELETE))
		{
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Mandatory Columns [resourceId or systemName] missing from CSV.";
		}
		
		if($bulkUploadResult->type && !in_array($bulkUploadResult->type, self::$validTypes))
		{
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Wrong value passed for property type [$bulkUploadResult->type]";
		}
		if(!$bulkUploadResult->type)
		{
			$bulkUploadResult->type = 'location';
		}
		
		if($bulkUploadResult->parentType && !in_array($bulkUploadResult->parentType, self::$validTypes))
		{
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Wrong value passed for property parentType [$bulkUploadResult->parentType]";
		}
		if(!$bulkUploadResult->parentType)
		{
			$bulkUploadResult->parentType = 'location';
		}
		
		if($this->maxRecords && $this->lineNumber > $this->maxRecords) // check max records
		{
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Exeeded max records count per bulk";
		}
		
		if($bulkUploadResult->status == VidiunBulkUploadResultStatus::ERROR)
		{
			$this->addBulkUploadResult($bulkUploadResult);
			return null;
		}
		
		return $bulkUploadResult;
	}
	
	/**
	 * Retrieve resources by system-names and replace the system-name with id
	 */
	protected function validateSystemNames($type, $filterSystemNames)
	{
		$schedulePlugin = VidiunScheduleClientPlugin::get(VBatchBase::$vClient);
		
		VBatchBase::impersonate($this->currentPartnerId);
		switch($type)
		{
			case 'location':
				$filter = new VidiunLocationScheduleResourceFilter();
				break;

			case 'camera':
				$filter = new VidiunCameraScheduleResourceFilter();
				break;

			case 'live_entry':
				$filter = new VidiunLiveEntryScheduleResourceFilter();
				break;
						
		}
		$filter->systemNameIn = implode(',', $filterSystemNames);
		$response = $schedulePlugin->scheduleResource->listAction($filter);
		foreach($response->objects as $scheduleResource)
		{
			if(!isset($this->existingSystemNames[$type]))
				$this->existingSystemNames[$type] = array();
			
			$this->existingSystemNames[$type][$scheduleResource->systemName] = $scheduleResource->id;
		}
		VBatchBase::unimpersonate();
	}
	
	/**
	 * Create the entries from the given bulk upload results
	 */
	protected function createObjects()
	{
		$schedulePlugin = VidiunScheduleClientPlugin::get(VBatchBase::$vClient);
		
		$filterSystemNames = array();
		foreach($this->bulkUploadResults as $bulkUploadResult)
		{
			if($bulkUploadResult->systemName && !$bulkUploadResult->resourceId && $bulkUploadResult->action != VidiunBulkUploadAction::ADD)
			{
				if(!isset($filterSystemNames[$bulkUploadResult->type]))
					$filterSystemNames[$bulkUploadResult->type] = array();
				
				$filterSystemNames[$bulkUploadResult->type][] = $bulkUploadResult->systemName;
				if(count($filterSystemNames[$bulkUploadResult->type] >= self::MAX_IN_FILTER))
				{
					$this->validateSystemNames($bulkUploadResult->type, $filterSystemNames[$bulkUploadResult->type]);
					$filterSystemNames[$bulkUploadResult->type] = array();
				}
			}
			
			if($bulkUploadResult->parentSystemName)
			{
				if(!isset($filterSystemNames[$bulkUploadResult->parentType]))
					$filterSystemNames[$bulkUploadResult->parentType] = array();
				
				$filterSystemNames[$bulkUploadResult->parentType][] = $bulkUploadResult->parentSystemName;
				if(count($filterSystemNames[$bulkUploadResult->parentType] >= self::MAX_IN_FILTER))
				{
					$this->validateSystemNames($bulkUploadResult->parentType, $filterSystemNames[$bulkUploadResult->parentType]);
					$filterSystemNames[$bulkUploadResult->parentType] = array();
				}
			}
		}
		foreach($filterSystemNames as $type => $names)
		{
			if(count($names))
				$this->validateSystemNames($type, $names);
		}
		
		// start a multi request for add entries
		VBatchBase::$vClient->startMultiRequest();
		
		VidiunLog::info("job[{$this->job->id}] start creating resources");
		$bulkUploadResultChunk = array(); // store the results of the created entries
		
		foreach($this->bulkUploadResults as $bulkUploadResult)
		{
			/* @var $bulkUploadResult VidiunBulkUploadResultScheduleResource */
			VidiunLog::info("Handling bulk upload result: [" . ($bulkUploadResult->resourceId ? $bulkUploadResult->resourceId : $bulkUploadResult->systemName) . "]");

			if(!$bulkUploadResult->resourceId && $bulkUploadResult->systemName && isset($this->existingSystemNames[$bulkUploadResult->type]))
			{
				$existingSystemNames = $this->existingSystemNames[$bulkUploadResult->type];
				if(isset($existingSystemNames[$bulkUploadResult->systemName]))
				{
					$bulkUploadResult->resourceId = $existingSystemNames[$bulkUploadResult->systemName];
				}
			}
			
			if($bulkUploadResult->action == VidiunBulkUploadAction::ADD_OR_UPDATE)
			{
				$bulkUploadResult->action = $bulkUploadResult->resourceId ? VidiunBulkUploadAction::UPDATE : VidiunBulkUploadAction::ADD;
			}
			
			
			VBatchBase::impersonate($this->currentPartnerId);
			switch($bulkUploadResult->action)
			{
				case VidiunBulkUploadAction::ADD:
					$scheduleResource = $this->createScheduleResourceFromResultAndJobData($bulkUploadResult);
					$bulkUploadResultChunk[] = $bulkUploadResult;
					if($scheduleResource)
					{
						$createdResource = $schedulePlugin->scheduleResource->add($scheduleResource);
						if($bulkUploadResult->systemName)
						{
							if(!isset($this->parentSystemNames[$bulkUploadResult->type]))
								$this->parentSystemNames[$bulkUploadResult->type] = array();
							
							$this->parentSystemNames[$bulkUploadResult->type][$bulkUploadResult->systemName] = "{$createdResource->id}";
						}
					}
					else 
					{
						VBatchBase::$vClient->system->ping(); // just to increment the multi-request index
					}
					break;
				
				case VidiunBulkUploadAction::UPDATE:
				
					$scheduleResource = null;
					if(!$bulkUploadResult->resourceId)
					{
						$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
						$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
						$bulkUploadResult->errorDescription = "Unable to find {$bulkUploadResult->type} resource [$bulkUploadResult->systemName]";
					}
					else
					{
						$scheduleResource = $this->createScheduleResourceFromResultAndJobData($bulkUploadResult);
					}
					$bulkUploadResultChunk[] = $bulkUploadResult;
					if($scheduleResource)
					{
						$schedulePlugin->scheduleResource->update($bulkUploadResult->resourceId, $scheduleResource);
						if($bulkUploadResult->resourceId && $bulkUploadResult->systemName)
						{
							if(!isset($this->existingSystemNames[$bulkUploadResult->type]))
								$this->existingSystemNames[$bulkUploadResult->type] = array();
							
							$this->existingSystemNames[$bulkUploadResult->type][$bulkUploadResult->systemName] = $bulkUploadResult->resourceId;
						}
					}
					else 
					{
						VBatchBase::$vClient->system->ping(); // just to increment the multi-request index
					}
					break;
				
				case VidiunBulkUploadAction::DELETE:
					if(!$bulkUploadResult->resourceId)
					{
						$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
						$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
						$bulkUploadResult->errorDescription = "Unable to find {$bulkUploadResult->type} resource [$bulkUploadResult->systemName]";
					}
					$bulkUploadResultChunk[] = $bulkUploadResult;
					if($bulkUploadResult->resourceId)
					{
						$schedulePlugin->scheduleResource->delete($bulkUploadResult->resourceId);
					}
					else 
					{
						VBatchBase::$vClient->system->ping(); // just to increment the multi-request index
					}
					break;
				
				default:
					$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
					$bulkUploadResult->errorDescription = "Unknown action passed: [" . $bulkUploadResult->action . "]";
					break;
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
		
		VidiunLog::info("job[{$this->job->id}] finish modifying resources");
	}
	
	/**
	 * Function to create a new schedule-resource from bulk upload result.
	 * 
	 * @param VidiunBulkUploadResultScheduleResource $bulkUploadResult   
	 * @return VidiunScheduleResource
	 */
	protected function createScheduleResourceFromResultAndJobData(VidiunBulkUploadResultScheduleResource &$bulkUploadResult)
	{
		switch($bulkUploadResult->type)
		{
			case 'location':
				$scheduleResource = new VidiunLocationScheduleResource();
				break;

			case 'camera':
				$scheduleResource = new VidiunCameraScheduleResource();
				break;

			case 'live_entry':
				$scheduleResource = new VidiunLiveEntryScheduleResource();
				break;
						
		}
		
		if($bulkUploadResult->name)
			$scheduleResource->name = $bulkUploadResult->name;
		
		if($bulkUploadResult->systemName)
			$scheduleResource->systemName = $bulkUploadResult->systemName;
		
		if($bulkUploadResult->description)
			$scheduleResource->description = $bulkUploadResult->description;
		
		if($bulkUploadResult->tags)
			$scheduleResource->tags = $bulkUploadResult->tags;

		if($bulkUploadResult->parentSystemName)
		{
			if(isset($this->existingSystemNames[$bulkUploadResult->parentType]) && isset($this->existingSystemNames[$bulkUploadResult->parentType][$bulkUploadResult->parentSystemName]))
			{
				$scheduleResource->parentId = $this->existingSystemNames[$bulkUploadResult->parentType][$bulkUploadResult->parentSystemName];
			}
			elseif(isset($this->parentSystemNames[$bulkUploadResult->parentType]) && isset($this->parentSystemNames[$bulkUploadResult->parentType][$bulkUploadResult->parentSystemName]))
			{
				$scheduleResource->parentId = $this->parentSystemNames[$bulkUploadResult->parentType][$bulkUploadResult->parentSystemName];
			}
			else
			{
				$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
				$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
				$bulkUploadResult->errorDescription = "Unable to find parent {$bulkUploadResult->parentType} resource [$bulkUploadResult->parentSystemName]";
				return null;
			}
		}
			
		return $scheduleResource;
	}
	
	/**
	 * {@inheritDoc}
	 * @see BulkUploadEngineCsv::getColumns()
	 */
	protected function getColumns()
	{
		return array(
			'action', 
			'resourceId', 
			'name',
			'type',
			'systemName',
			'description',
			'tags',
			'parentType',
			'parentSystemName',
		);
	}
	
	/**
	 * {@inheritDoc}
	 * @see VBulkUploadEngine::updateObjectsResults()
	 */
	protected function updateObjectsResults(array $requestResults, array $bulkUploadResults)
	{
		VBatchBase::$vClient->startMultiRequest();
		
		// checking the created entries
		foreach($requestResults as $index => $requestResult)
		{
			$bulkUploadResult = $bulkUploadResults[$index];
			
			if(is_array($requestResult) && isset($requestResult['code']))
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
			
			if($requestResult instanceof VidiunScheduleResource)
			{
				if ($requestResult->id)
				    $bulkUploadResult->objectId = $requestResult->id;

				if($requestResult->systemName && isset($this->parentSystemNames[$bulkUploadResult->type]) && isset($this->parentSystemNames[$bulkUploadResult->type][$requestResult->systemName]))
				{
					if(!isset($this->existingSystemNames[$bulkUploadResult->type]))
						$this->existingSystemNames[$bulkUploadResult->type] = array();
					
					$this->existingSystemNames[$bulkUploadResult->type][$requestResult->systemName] = $requestResult->id;
					unset($this->parentSystemNames[$bulkUploadResult->type][$requestResult->systemName]);
				}
			}
			
			$this->addBulkUploadResult($bulkUploadResult);
		}
		
		VBatchBase::$vClient->doMultiRequest();
	}
	
	/**
	 * {@inheritDoc}
	 * @see BulkUploadEngineCsv::getUploadResultInstance()
	 */
	protected function getUploadResultInstance()
	{
		return new VidiunBulkUploadResultScheduleResource();
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
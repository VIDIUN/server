<?php
/**
 * Class which parses the bulk upload CSV and creates the objects listed in it. 
 * This engine class parses CSVs which describe category users.
 * 
 * @package plugins.bulkUploadCsv
 * @subpackage batch
 */
class BulkUploadCategoryUserEngineCsv extends BulkUploadEngineCsv
{
	const OBJECT_TYPE_TITLE = 'entitlement';
	
    private $categoryReferenceIdMap = array();
    
	/**
     * (non-PHPdoc)
     * @see BulkUploadGeneralEngineCsv::createUploadResult()
     */
    protected function createUploadResult($values, $columns)
	{
	    $bulkUploadResult = parent::createUploadResult($values, $columns);
	    if (!$bulkUploadResult)
	    	return;
	    
		$bulkUploadResult->bulkUploadResultObjectType = VidiunBulkUploadObjectType::CATEGORY_USER;
				
		// trim the values
		array_walk($values, array('BulkUploadUserEngineCsv', 'trimArray'));
		
		// sets the result values
		foreach($columns as $index => $column)
		{
			if(!is_numeric($index))
				continue;
            
			
		    if ($column == 'status' && $values[$index] != VidiunCategoryUserStatus::PENDING)
			{
			    $bulkUploadResult->requiredObjectStatus = $values[$index];
			}
			
			if(iconv_strlen($values[$index], 'UTF-8'))
			{
				$bulkUploadResult->$column = $values[$index];
				VidiunLog::info("Set value $column [{$bulkUploadResult->$column}]");
			}
			else
			{
				VidiunLog::info("Value $column is empty");
			}
		}
		
		if(isset($columns['plugins']))
		{
			$bulkUploadPlugins = array();
			
			foreach($columns['plugins'] as $index => $column)
			{
				$bulkUploadPlugin = new VidiunBulkUploadPluginData();
				$bulkUploadPlugin->field = $column;
				$bulkUploadPlugin->value = iconv_strlen($values[$index], 'UTF-8') ? $values[$index] : null;
				$bulkUploadPlugins[] = $bulkUploadPlugin;
				
				VidiunLog::info("Set plugin value $column [{$bulkUploadPlugin->value}]");
			}
			
			$bulkUploadResult->pluginsData = $bulkUploadPlugins;
		}
		
		$bulkUploadResult->objectStatus = VidiunCategoryUserStatus::ACTIVE;
		$bulkUploadResult->status = VidiunBulkUploadResultStatus::IN_PROGRESS;
		
		if (!$bulkUploadResult->action)
		{
		    $bulkUploadResult->action = VidiunBulkUploadAction::ADD;
		}
		
		$bulkUploadResult = $this->validateBulkUploadResult($bulkUploadResult);
		
		$this->bulkUploadResults[] = $bulkUploadResult;
	}
    
	protected function validateBulkUploadResult (VidiunBulkUploadResult $bulkUploadResult)
	{
	    /* @var $bulkUploadResult VidiunBulkUploadResultCategoryUser */
		if (!$bulkUploadResult->userId)
		{
		    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Missing mandatory parameter userId";
		}
		
		if (!$bulkUploadResult->categoryId && !$bulkUploadResult->categoryReferenceId)
		{
		    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Missing mandatory parameter categoryId";
		}
		
		if ($bulkUploadResult->requiredObjectStatus && !$this->isValidEnumValue('VidiunCategoryUserStatus', $bulkUploadResult->requiredObjectStatus))
	    {
	        $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Wrong value passed for property status.";
	    }
	    		
		if ($bulkUploadResult->permissionLevel && !$this->isValidEnumValue('VidiunCategoryUserPermissionLevel', $bulkUploadResult->permissionLevel))
	    {
	        $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Wrong value passed for property permissionLevel.";
	    }
	    		
		if ($bulkUploadResult->updateMethod && !$this->isValidEnumValue('VidiunUpdateMethodType', $bulkUploadResult->updateMethod))
	    {
	        $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Wrong value passed for property updateMethod.";
	    }
	    
	    
		if($this->maxRecords && $this->lineNumber > $this->maxRecords) // check max records
		{
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Exceeded max records count per bulk";
		}
		
		if (!$bulkUploadResult->categoryId && $bulkUploadResult->categoryReferenceId)
		{
		    $filter = new VidiunCategoryFilter();
		    $filter->referenceIdEqual = $bulkUploadResult->categoryReferenceId;
		    VBatchBase::impersonate($this->currentPartnerId);;
		    $categoryResults = VBatchBase::$vClient->category->listAction($filter);
		    VBatchBase::unimpersonate();
		    
		    if ($categoryResults->objects && count($categoryResults->objects))
		    {
		        $bulkUploadResult->categoryId = $categoryResults->objects[0]->id;
		        $this->categoryReferenceIdMap[$bulkUploadResult->categoryReferenceId] = $bulkUploadResult->categoryId;
		    }
		    else
		    {
		        $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
		        $bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
		        $bulkUploadResult->errorDescription = "Could not locate category by given reference ID.";
		    }
		}
        
	    if ($bulkUploadResult->action == VidiunBulkUploadAction::ADD_OR_UPDATE && $bulkUploadResult->status != VidiunBulkUploadResultStatus::ERROR)
		{
		    try 
		    {
		        VBatchBase::impersonate($this->currentPartnerId);;
		        $categoryUser = VBatchBase::$vClient->categoryUser->get($bulkUploadResult->categoryId, $bulkUploadResult->userId);
                VBatchBase::unimpersonate();
		        $bulkUploadResult->action = VidiunBulkUploadAction::UPDATE;
		    }
		    catch (Exception $e)
		    {
		        $bulkUploadResult->action = VidiunBulkUploadAction::ADD;
		        VBatchBase::unimpersonate();
		    }
		}
			
		if($bulkUploadResult->status == VidiunBulkUploadResultStatus::ERROR)
		{
			$this->addBulkUploadResult($bulkUploadResult);
			return;
		}	

		
		return $bulkUploadResult;
	}
	
	
    protected function addBulkUploadResult(VidiunBulkUploadResult $bulkUploadResult)
	{
		parent::addBulkUploadResult($bulkUploadResult);
		
	}
	/**
	 * 
	 * Create the entries from the given bulk upload results
	 */
	protected function createObjects()
	{
		// start a multi request for add entries
		VBatchBase::impersonate($this->currentPartnerId);;
		VBatchBase::$vClient->startMultiRequest();
		
		VidiunLog::info("job[{$this->job->id}] start creating users");
		$bulkUploadResultChunk = array(); // store the results of the created entries
				
		
		foreach($this->bulkUploadResults as $bulkUploadResult)
		{
			/* @var $bulkUploadResult VidiunBulkUploadResultCategoryUser */
		    switch ($bulkUploadResult->action)
		    {
		        case VidiunBulkUploadAction::ADD:
    		        $user = $this->createCategoryUserFromResultAndJobData($bulkUploadResult);
        					
        			$bulkUploadResultChunk[] = $bulkUploadResult;
        			
        			
        			$categoryUser = VBatchBase::$vClient->categoryUser->add($user);
		            break;
		        
		        case VidiunBulkUploadAction::UPDATE:
		            $categoryUser = $this->createCategoryUserFromResultAndJobData($bulkUploadResult);
        					
        			$bulkUploadResultChunk[] = $bulkUploadResult;
        			VBatchBase::$vClient->categoryUser->update($bulkUploadResult->categoryId, $bulkUploadResult->userId, $categoryUser);
		            break;
		            
		        case VidiunBulkUploadAction::DELETE:
		            $bulkUploadResultChunk[] = $bulkUploadResult;
        			$bulkUploadResult->requiredObjectStatus = null;
        			VBatchBase::$vClient->categoryUser->delete($bulkUploadResult->categoryId, $bulkUploadResult->userId);
        			
		            break;
		        
		        default:
		            $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
		            $bulkUploadResult->errorDescription = "Unknown action passed: [".$bulkUploadResult->action ."]";
		            break;
		    }
		    
		    if(VBatchBase::$vClient->getMultiRequestQueueSize() >= $this->multiRequestSize)
			{
				// handle all categoryUser objects as the partner
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
		
		if(count($requestResults))
			$this->updateObjectsResults($requestResults, $bulkUploadResultChunk);

		VidiunLog::info("job[{$this->job->id}] finish modifying users");
	}
	
	/**
	 * Function to create a new category user from bulk upload result.
	 * @param VidiunBulkUploadResultCategoryUser $bulkUploadCategoryUserResult
	 */
	protected function createCategoryUserFromResultAndJobData (VidiunBulkUploadResultCategoryUser $bulkUploadCategoryUserResult)
	{
	    $categoryUser = new VidiunCategoryUser();
	    //calculate parentId of the category
	    
	    if ($bulkUploadCategoryUserResult->categoryId)
	    {
	        $categoryUser->categoryId = $bulkUploadCategoryUserResult->categoryId;
	    }
	    else if ($this->categoryReferenceIdMap[$bulkUploadCategoryUserResult->categoryReferenceId])
	    {
	        $categoryUser->categoryId = $this->categoryReferenceIdMap[$bulkUploadCategoryUserResult->categoryReferenceId];
	    }
	    
	    if ($bulkUploadCategoryUserResult->userId)
	        $categoryUser->userId = $bulkUploadCategoryUserResult->userId;
	        
	    if (!is_null($bulkUploadCategoryUserResult->permissionLevel))
	        $categoryUser->permissionLevel = $bulkUploadCategoryUserResult->permissionLevel;
	        
	    $categoryUser->updateMethod = VidiunUpdateMethodType::AUTOMATIC;
	    if (!is_null($bulkUploadCategoryUserResult->updateMethod))
	        $categoryUser->updateMethod = $bulkUploadCategoryUserResult->updateMethod; 
	        
	    return $categoryUser;
	}
	
	/**
	 * 
	 * Gets the columns for CSV file
	 */
	protected function getColumns()
	{
		return array(
		    "action",
		    "categoryUserId",
		    "categoryId",
		    "categoryReferenceId",
		    "userId",
			"status",
		    "permissionLevel",
		    "updateMethod",
		);
	}
	
	
    protected function updateObjectsResults(array $requestResults, array $bulkUploadResults)
	{
		VidiunLog::info("Updating " . count($requestResults) . " results");
		
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
			
			if ($bulkUploadResult->action != VidiunBulkUploadAction::DELETE)
			    $bulkUploadResult = $this->changeCategoryVuserStatus($bulkUploadResult);
			$this->addBulkUploadResult($bulkUploadResult);
		}
		
	}
	
	protected function changeCategoryVuserStatus (VidiunBulkUploadResultCategoryUser $bulkuploadResult)
	{
	      if ($bulkuploadResult->status != VidiunBulkUploadResultStatus::ERROR)
	      {
	          VBatchBase::impersonate($this->currentPartnerId);;
	          switch ($bulkuploadResult->requiredObjectStatus)
	          {
	              case VidiunCategoryUserStatus::ACTIVE:
	                  try {
	                      VBatchBase::$vClient->categoryUser->activate($bulkuploadResult->categoryId, $bulkuploadResult->userId);
	                  }
	                  catch (Exception $e)
	                  {
	                      $bulkuploadResult->errorDescription .= $e->getMessage();
	                  }
	                  break;
	              case VidiunCategoryUserStatus::NOT_ACTIVE:
	                  try {
	                      VBatchBase::$vClient->categoryUser->deactivate($bulkuploadResult->categoryId, $bulkuploadResult->userId);
	                  }
	                  catch (Exception $e)
	                  {
	                      $bulkuploadResult->errorDescription .= $e->getMessage();
	                  }
	                  break;
	          }
	          VBatchBase::unimpersonate();
	      }
	      else
	      {
	          $bulkuploadResult->errorDescription .= 'Cannot update status - VidiunCategoryUser object was not created.';
	      }
          	      
	      return $bulkuploadResult;
	}
	
	protected function getUploadResultInstance ()
	{
	    return new VidiunBulkUploadResultCategoryUser();
	}
	
	public function getObjectTypeTitle()
	{
		return self::OBJECT_TYPE_TITLE;
	}
}
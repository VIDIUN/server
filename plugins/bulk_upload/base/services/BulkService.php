<?php
/**
 * Bulk upload service is used to upload & manage bulk uploads
 *
 * @service bulk
 * @package plugins.bulkUpload
 * @subpackage services
 */
class BulkService extends VidiunBaseService
{
	const PARTNER_DEFAULT_CONVERSION_PROFILE_ID = -1;
	
	const SERVICE_NAME = "bulkUpload";

	/**
	 * Add new bulk upload batch job
	 * Conversion profile id can be specified in the API or in the CSV file, the one in the CSV file will be stronger.
	 * If no conversion profile was specified, partner's default will be used
	 * 
	 * @action addEntries
	 * @actionAlias media.bulkUploadAdd
	 * @param file $fileData
	 * @param VidiunBulkUploadType $bulkUploadType
	 * @param VidiunBulkUploadJobData $bulkUploadData
	 * @return VidiunBulkUpload
	 */
	function addEntriesAction($fileData, VidiunBulkUploadJobData $bulkUploadData = null, VidiunBulkUploadEntryData $bulkUploadEntryData = null)
	{
		if(get_class($bulkUploadData) == 'VidiunBulkUploadJobData')
			throw new VidiunAPIException(VidiunErrors::OBJECT_TYPE_ABSTRACT, 'VidiunBulkUploadJobData');
			
	    if($bulkUploadEntryData->conversionProfileId == self::PARTNER_DEFAULT_CONVERSION_PROFILE_ID)
			$bulkUploadEntryData->conversionProfileId = $this->getPartner()->getDefaultConversionProfileId();
	    
	    if (!$bulkUploadData)
	    {
	       $bulkUploadData = VidiunPluginManager::loadObject('VidiunBulkUploadJobData', null);
	    }
	    
	    if (!$bulkUploadEntryData)
	    {
	        $bulkUploadEntryData = new VidiunBulkUploadEntryData();
	    }
		if(!$bulkUploadData->fileName)
			$bulkUploadData->fileName = $fileData["name"];
		
		$dbBulkUploadJobData = $bulkUploadData->toInsertableObject();
		$bulkUploadCoreType = vPluginableEnumsManager::apiToCore("BulkUploadType", $bulkUploadData->type);
		/* @var $dbBulkUploadJobData vBulkUploadJobData */
		$dbBulkUploadJobData->setBulkUploadObjectType(BulkUploadObjectType::ENTRY);
		$dbBulkUploadJobData->setUserId($this->getVuser()->getPuserId());
		$dbObjectData = $bulkUploadEntryData->toInsertableObject();
		$dbBulkUploadJobData->setObjectData($dbObjectData);
		$dbBulkUploadJobData->setFilePath($fileData["tmp_name"]);
		
		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $dbBulkUploadJobData, $bulkUploadCoreType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		if(!$dbJobLog)
			return null;
			
		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());
		
		return $bulkUpload;
	}
	
	/**
	 * @action addCategories
	 * @actionAlias category.addFromBulkUpload
	 * 
	 * Action adds categories from a bulkupload CSV file
	 * @param file $fileData
	 * @param VidiunBulkUploadJobData $bulkUploadData
	 * @param VidiunBulkUploadCategoryData $bulkUploadCategoryData
	 * @return VidiunBulkUpload
	 */
	public function addCategoriesAction ($fileData, VidiunBulkUploadJobData $bulkUploadData = null, VidiunBulkUploadCategoryData $bulkUploadCategoryData = null)
	{
	    if (!$bulkUploadData)
	    {
	       $bulkUploadData = VidiunPluginManager::loadObject('VidiunBulkUploadJobData', null);
	    }
	    
	    if (!$bulkUploadCategoryData)
	    {
	        $bulkUploadCategoryData = new VidiunBulkUploadCategoryData();
	    }
	    
		if(!$bulkUploadData->fileName)
			$bulkUploadData->fileName = $fileData["name"];
		
		$dbBulkUploadJobData = $bulkUploadData->toInsertableObject();
		$bulkUploadCoreType = vPluginableEnumsManager::apiToCore("BulkUploadType", $bulkUploadData->type);
		
		$dbBulkUploadJobData->setBulkUploadObjectType(BulkUploadObjectType::CATEGORY);
		$dbBulkUploadJobData->setUserId($this->getVuser()->getPuserId());
		$dbObjectData = $bulkUploadCategoryData->toInsertableObject();
		$dbBulkUploadJobData->setObjectData($dbObjectData);
		$dbBulkUploadJobData->setFilePath($fileData["tmp_name"]);
		
		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $dbBulkUploadJobData, $bulkUploadCoreType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		if(!$dbJobLog)
			return null;
		
		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());
		
		return $bulkUpload;
	}
	
	/**
	 * @action addCategoryUsers
	 * @actionAlias categoryUser.addFromBulkUpload
	 * Action adds CategoryUsers from a bulkupload CSV file
	 * @param file $fileData
	 * @param VidiunBulkUploadJobData $bulkUploadData
	 * @param VidiunBulkUploadCategoryUserData $bulkUploadCategoryUserData
	 * @return VidiunBulkUpload
	 */
	public function addCategoryUsersAction ($fileData, VidiunBulkUploadJobData $bulkUploadData = null, VidiunBulkUploadCategoryUserData $bulkUploadCategoryUserData = null)
	{
	    if (!$bulkUploadData)
	    {
	       $bulkUploadData = VidiunPluginManager::loadObject('VidiunBulkUploadJobData', null);
	    }
	    
        if (!$bulkUploadCategoryUserData)
        {
            $bulkUploadCategoryUserData = new VidiunBulkUploadCategoryUserData();
        }
		
		if(!$bulkUploadData->fileName)
			$bulkUploadData->fileName = $fileData["name"];
		
		$dbBulkUploadJobData = $bulkUploadData->toInsertableObject();
		$bulkUploadCoreType = vPluginableEnumsManager::apiToCore("BulkUploadType", $bulkUploadData->type);
		$dbBulkUploadJobData->setBulkUploadObjectType(BulkUploadObjectType::CATEGORY_USER);
		$dbBulkUploadJobData->setUserId($this->getVuser()->getPuserId());
		$dbObjectData = $bulkUploadCategoryUserData->toInsertableObject();
		$dbBulkUploadJobData->setObjectData($dbObjectData);
		$dbBulkUploadJobData->setFilePath($fileData["tmp_name"]);
		
		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $dbBulkUploadJobData, $bulkUploadCoreType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		if(!$dbJobLog)
			return null;
		
		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());
		
		return $bulkUpload;
	}
	
	/**
	 * @action addUsers
	 * @actionAlias user.addFromBulkUpload
	 * Action adds users from a bulkupload CSV file
	 * @param file $fileData
	 * @param VidiunBulkUploadJobData $bulkUploadData
	 * @param VidiunBulkUploadUserData $bulkUploadUserData
	 * @return VidiunBulkUpload
	 */
	public function addUsersAction($fileData, VidiunBulkUploadJobData $bulkUploadData = null, VidiunBulkUploadUserData $bulkUploadUserData = null)
	{
	   if (!$bulkUploadData)
	   {
	       $bulkUploadData = VidiunPluginManager::loadObject('VidiunBulkUploadJobData', null);
	   }
	   
	   if (!$bulkUploadUserData)
	   {
	       $bulkUploadUserData = new VidiunBulkUploadUserData();
	   }
		
		if(!$bulkUploadData->fileName)
			$bulkUploadData->fileName = $fileData["name"];
		
		$dbBulkUploadJobData = $bulkUploadData->toInsertableObject();
		$bulkUploadCoreType = vPluginableEnumsManager::apiToCore("BulkUploadType", $bulkUploadData->type);
		$dbBulkUploadJobData->setBulkUploadObjectType(BulkUploadObjectType::USER);
		$dbBulkUploadJobData->setUserId($this->getVuser()->getPuserId());
		$dbObjectData = $bulkUploadUserData->toInsertableObject();
		$dbBulkUploadJobData->setObjectData($dbObjectData);
		$dbBulkUploadJobData->setFilePath($fileData["tmp_name"]);
		
		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $dbBulkUploadJobData, $bulkUploadCoreType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		if(!$dbJobLog)
			return null;
			
		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());
		
		return $bulkUpload;
	}
	
	/**
	 * @action addCategoryEntries
	 * @actionAlias categoryEntry.addFromBulkUpload
	 * Action adds active category entries
	 * @param VidiunBulkServiceData $bulkUploadData
	 * @param VidiunBulkUploadCategoryEntryData $bulkUploadCategoryEntryData
	 * @return VidiunBulkUpload
	 */
	public function addCategoryEntriesAction (VidiunBulkServiceData $bulkUploadData, VidiunBulkUploadCategoryEntryData $bulkUploadCategoryEntryData = null)
	{
		if($bulkUploadData instanceof  VidiunBulkServiceFilterData){
			if($bulkUploadData->filter instanceof VidiunBaseEntryFilter){
				if(	$bulkUploadData->filter->idEqual == null &&
					$bulkUploadData->filter->idIn == null &&
					$bulkUploadData->filter->categoriesIdsMatchOr == null &&
					$bulkUploadData->filter->categoriesMatchAnd == null &&
					$bulkUploadData->filter->categoriesMatchOr == null &&
					$bulkUploadData->filter->categoriesIdsMatchAnd == null)
						throw new VidiunAPIException(VidiunErrors::MUST_FILTER_ON_ENTRY_OR_CATEGORY);					
			}
			else if($bulkUploadData->filter instanceof VidiunCategoryEntryFilter){
				if(	$bulkUploadData->filter->entryIdEqual == null &&
					$bulkUploadData->filter->categoryIdIn == null &&
					$bulkUploadData->filter->categoryIdEqual == null )
						throw new VidiunAPIException(VidiunErrors::MUST_FILTER_ON_ENTRY_OR_CATEGORY);				
			}
		}
	   	$bulkUploadJobData = VidiunPluginManager::loadObject('VidiunBulkUploadJobData', $bulkUploadData->getType());
	   	$bulkUploadData->toBulkUploadJobData($bulkUploadJobData);
	    
        if (!$bulkUploadCategoryEntryData)
        {
            $bulkUploadCategoryEntryData = new VidiunBulkUploadCategoryEntryData();
        }
				
		$dbBulkUploadJobData = $bulkUploadJobData->toInsertableObject();
		$bulkUploadCoreType = vPluginableEnumsManager::apiToCore("BulkUploadType", $bulkUploadJobData->type);
		$dbBulkUploadJobData->setBulkUploadObjectType(BulkUploadObjectType::CATEGORY_ENTRY);
		$dbBulkUploadJobData->setUserId($this->getVuser()->getPuserId());
		$dbObjectData = $bulkUploadCategoryEntryData->toInsertableObject();
		$dbBulkUploadJobData->setObjectData($dbObjectData);
		
		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $dbBulkUploadJobData, $bulkUploadCoreType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		if(!$dbJobLog)
			return null;
		
		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());
		
		return $bulkUpload;
	}	
	
	/**
	 * Get bulk upload batch job by id
	 *
	 * @action get
	 * @param int $id
	 * @return VidiunBulkUpload
	 */
	function getAction($id)
	{
	    $c = new Criteria();
	    $c->addAnd(BatchJobLogPeer::JOB_ID, $id);
		$c->addAnd(BatchJobLogPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobLogPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		$batchJob = BatchJobLogPeer::doSelectOne($c);
		
		if (!$batchJob)
		    throw new VidiunAPIException(VidiunErrors::BULK_UPLOAD_NOT_FOUND, $id);
		    
		$ret = new VidiunBulkUpload();
		$ret->fromObject($batchJob, $this->getResponseProfile());
		return $ret;
	}
	
	/**
	 * List bulk upload batch jobs
	 *
	 * @action list
	 * @param VidiunBulkUploadFilter $bulkUploadFilter
	 * @param VidiunFilterPager $pager
	 * @return VidiunBulkUploadListResponse
	 */
	function listAction(VidiunBulkUploadFilter $bulkUploadFilter = null, VidiunFilterPager $pager = null)
	{
		if (!$bulkUploadFilter)
		$bulkUploadFilter = new VidiunBulkUploadFilter();
	    
		if (!$pager)
			$pager = new VidiunFilterPager();
		
 		$response = new VidiunBulkUploadListResponse();

		$coreBulkUploadFilter = new BatchJobLogFilter();
        	$bulkUploadFilter->toObject($coreBulkUploadFilter);
			
	    	$c = new Criteria();
		
		// when filtering the last hour logs, limit list to last 30K records in order to constrain query performance
		if ($bulkUploadFilter->uploadedOnGreaterThanOrEqual)// && $bulkUploadFilter->uploadedOnGreaterThanOrEqual > time() - 3600)
		{
			$c2 = new Criteria();
			$c2->addDescendingOrderByColumn(BatchJobLogPeer::ID);
			$lastLog = BatchJobLogPeer::doSelectOne($c2);
			if (!$lastLog)
				return $response;

			$lastId = $lastLog->getId() - 300000;
			$c->addAnd(BatchJobLogPeer::ID, $lastId, Criteria::GREATER_THAN);
		}
		
		$c->addAnd(BatchJobLogPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobLogPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		
		$crit = $c->getNewCriterion(BatchJobLogPeer::ABORT, null);
		$critOr = $c->getNewCriterion(BatchJobLogPeer::ABORT, 0);
		$crit->addOr($critOr);
		$c->add($crit);
		
		$c->addDescendingOrderByColumn(BatchJobLogPeer::ID);
		
		$coreBulkUploadFilter->attachToCriteria($c);
		$count = BatchJobLogPeer::doCount($c);
		$pager->attachToCriteria($c);
		$jobs = BatchJobLogPeer::doSelect($c);
		
		$response->objects = VidiunBulkUploads::fromBatchJobArray($jobs);
		$response->totalCount = $count; 
		
		return $response;
	}
	
	
	
	
	/**
	 * serve action returns the original file.
	 * 
	 * @action serve
	 * @param int $id job id
	 * @return file
	 * 
	 */
	function serveAction($id)
	{
		$c = new Criteria();
		$c->addAnd(BatchJobPeer::ID, $id);
		$c->addAnd(BatchJobPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		$batchJob = BatchJobPeer::doSelectOne($c);
		
		if (!$batchJob)
			throw new VidiunAPIException(VidiunErrors::BULK_UPLOAD_NOT_FOUND, $id);
			 
		VidiunLog::info("Batch job found for jobid [$id] bulk upload type [". $batchJob->getJobSubType() . "]");

		$syncKey = $batchJob->getSyncKey(BatchJob::FILE_SYNC_BATCHJOB_SUB_TYPE_BULKUPLOAD);
		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
		
		header("Content-Type: text/plain; charset=UTF-8");

		if($local)
		{
			$filePath = $fileSync->getFullPath();
			$mimeType = vFile::mimeType($filePath);
			return $this->dumpFile($filePath, $mimeType);
		}
		else
		{
			$remoteUrl = vDataCenterMgr::getRedirectExternalUrl($fileSync);
			VidiunLog::info("Redirecting to [$remoteUrl]");
			header("Location: $remoteUrl");
			die;
		}	
	}
	
	
	/**
	 * serveLog action returns the log file for the bulk-upload job.
	 * 
	 * @action serveLog
	 * @param int $id job id
	 * @return file
	 * 
	 */
	function serveLogAction($id)
	{
		$c = new Criteria();
		$c->addAnd(BatchJobPeer::ID, $id);
		$c->addAnd(BatchJobPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		$batchJob = BatchJobPeer::doSelectOne($c);
		
		if (!$batchJob)
			throw new VidiunAPIException(VidiunErrors::BULK_UPLOAD_NOT_FOUND, $id);
			 
		VidiunLog::info("Batch job found for jobid [$id] bulk upload type [". $batchJob->getJobSubType() . "]");
			
		$pluginInstances = VidiunPluginManager::getPluginInstances('IVidiunBulkUpload');
		foreach($pluginInstances as $pluginInstance)
		{
			/* @var $pluginInstance IVidiunBulkUpload */
			$pluginInstance->writeBulkUploadLogFile($batchJob);
		}	
	}
	
	/**
	 * Aborts the bulk upload and all its child jobs
	 * 
	 * @action abort
	 * @param int $id job id
	 * @return VidiunBulkUpload
	 */
	function abortAction($id)
	{
	    $c = new Criteria();
	    $c->addAnd(BatchJobPeer::ID, $id);
		$c->addAnd(BatchJobPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		$batchJob = BatchJobPeer::doSelectOne($c);
		
	    if (!$batchJob)
		    throw new VidiunAPIException(VidiunErrors::BULK_UPLOAD_NOT_FOUND, $id);
		
		vJobsManager::abortJob($id, BatchJobType::BULKUPLOAD, true);
		
		$batchJobLog = BatchJobLogPeer::retrieveByBatchJobId($id);
		
		$ret = new VidiunBulkUpload();
		if ($batchJobLog)
    		$ret->fromObject($batchJobLog, $this->getResponseProfile());
    	
    	return $ret;
	}

	/**
	 * @action updateCategoryEntriesStatus
	 * @actionAlias categoryEntry.updateStatusFromBulk
	 * Action activate or rejects categoryEntry objects from a bulkupload CSV file
	 * @param file $fileData
	 * @param VidiunBulkUploadJobData $bulkUploadData
	 * @param VidiunBulkUploadCategoryEntryData $bulkUploadCategoryEntryData
	 * @return VidiunBulkUpload
	 */
	public function updateCategoryEntriesStatusAction($fileData, VidiunBulkUploadJobData $bulkUploadData = null, VidiunBulkUploadCategoryEntryData $bulkUploadCategoryEntryData = null)
	{
		if (!$bulkUploadData)
		{
			$bulkUploadData = VidiunPluginManager::loadObject('VidiunBulkUploadJobData', null);
		}

		if (!$bulkUploadCategoryEntryData)
		{
			$bulkUploadCategoryEntryData = new VidiunBulkUploadCategoryEntryData();
		}

		if(!$bulkUploadData->fileName)
		{
			$bulkUploadData->fileName = $fileData["name"];
		}

		$dbBulkUploadJobData = $bulkUploadData->toInsertableObject();
		$bulkUploadCoreType = vPluginableEnumsManager::apiToCore("BulkUploadType", $bulkUploadData->type);
		$dbBulkUploadJobData->setBulkUploadObjectType(BulkUploadObjectType::CATEGORY_ENTRY);
		$dbBulkUploadJobData->setUserId($this->getVuser()->getPuserId());
		$dbObjectData = $bulkUploadCategoryEntryData->toInsertableObject();
		$dbBulkUploadJobData->setObjectData($dbObjectData);
		$dbBulkUploadJobData->setFilePath($fileData["tmp_name"]);

		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $dbBulkUploadJobData, $bulkUploadCoreType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		if(!$dbJobLog)
		{
			return null;
		}

		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());

		return $bulkUpload;
	}

	/**
	 * @action userEntryBulkDelete
	 * @actionAlias userEntry.bulkDelete
	 * Action delete userEntry objects from filter in bulk
	 * @param VidiunUserEntryFilter $filter
	 * @throws VidiunErrors::FAILED_TO_CREATE_BULK_DELETE
	 * @return int
	 */
	public function userEntryBulkDeleteAction(VidiunUserEntryFilter $filter)
	{
		$bulkUploadData = new VidiunBulkServiceFilterDataBase();
		$bulkUploadData->filter = $filter;
		$bulkUploadObjectType = BulkUploadObjectType::USER_ENTRY;
		$bulkUpload = $this->bulkDelete($bulkUploadData, $bulkUploadObjectType);
		return $bulkUpload->id;
	}

	protected function bulkDelete(VidiunBulkServiceFilterDataBase $bulkUploadData, $bulkUploadObjectType)
	{
		$bulkUploadJobData = VidiunPluginManager::loadObject('VidiunBulkUploadJobData', $bulkUploadData->getType());
		$bulkUploadData->toBulkUploadJobData($bulkUploadJobData);

		$dbBulkUploadJobData = $bulkUploadJobData->toInsertableObject();
		$bulkUploadCoreType = vPluginableEnumsManager::apiToCore("BulkUploadType", $bulkUploadJobData->type);
		$dbBulkUploadJobData->setBulkUploadObjectType($bulkUploadObjectType);
		$dbBulkUploadJobData->setUserId($this->getVuser()->getPuserId());

		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $dbBulkUploadJobData, $bulkUploadCoreType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		if(!$dbJobLog)
		{
			throw new VidiunAPIException(VidiunErrors::FAILED_TO_CREATE_BULK_DELETE);
		}

		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());

		return $bulkUpload;
	}


}

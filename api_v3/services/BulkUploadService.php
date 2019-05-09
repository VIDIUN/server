<?php

/**
 * Bulk upload service is used to upload & manage bulk uploads using CSV files.
 * This service manages only entry bulk uploads.
 *
 * @service bulkUpload
 * @package api
 * @subpackage services
 * @deprecated Use BulkUploadPlugin instead.
 */
class BulkUploadService extends VidiunBaseService
{
	const PARTNER_DEFAULT_CONVERSION_PROFILE_ID = -1;

	/**
	 * Add new bulk upload batch job
	 * Conversion profile id can be specified in the API or in the CSV file, the one in the CSV file will be stronger.
	 * If no conversion profile was specified, partner's default will be used
	 * 
	 * @action add
	 * @param int $conversionProfileId Conversion profile id to use for converting the current bulk (-1 to use partner's default)
	 * @param file $csvFileData bulk upload file
	 * @param VidiunBulkUploadType $bulkUploadType
	 * @param string $uploadedBy
	 * @param string $fileName Friendly name of the file, used to be recognized later in the logs.
	 * @return VidiunBulkUpload
	 */
	public function addAction($conversionProfileId, $csvFileData, $bulkUploadType = null, $uploadedBy = null, $fileName = null)
	{
		if($conversionProfileId == self::PARTNER_DEFAULT_CONVERSION_PROFILE_ID)
			$conversionProfileId = $this->getPartner()->getDefaultConversionProfileId();
			
		$conversionProfile = conversionProfile2Peer::retrieveByPK($conversionProfileId);
		if(!$conversionProfile)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId);
		
		$coreBulkUploadType = vPluginableEnumsManager::apiToCore('BulkUploadType', $bulkUploadType);
		
		if(is_null($uploadedBy))
			$uploadedBy = $this->getVuser()->getPuserId();
		
		if(!$fileName)
			$fileName = $csvFileData["name"];
		
		$data = $this->constructJobData($csvFileData["tmp_name"], $fileName, $this->getPartner(), $this->getVuser()->getPuserId(), $uploadedBy, $conversionProfileId, $coreBulkUploadType);
		
		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $data, $coreBulkUploadType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());
		
		return $bulkUpload;
	}
	
	/**
	 * Function constructs a core object of type vBulkUploadJobData
	 * @param int $conversionProfileId
	 * @param string $filePath
	 * @param string $userId
	 * @param int $bulkUploadType
	 * @param string $uploadedBy
	 * @param string $fileName
	 * @throws VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND
	 */
	protected function constructJobData ($filePath, $fileName, Partner $partner, $puserId, $uploadedBy, $conversionProfileId = null, $coreBulkUploadType = null)
	{
	   $data = VidiunPluginManager::loadObject('vBulkUploadJobData', $coreBulkUploadType);

		if(is_null($data))
		{
			throw new VidiunAPIException(VidiunErrors::BULK_UPLOAD_BULK_UPLOAD_TYPE_NOT_VALID, $coreBulkUploadType);
		}
		
		$data->setFilePath($filePath);
		$data->setUserId($puserId);
		$data->setUploadedBy($uploadedBy);
		$data->setFileName($fileName);
		$data->handleVsPrivileges();

		if (!$conversionProfileId)
		{
			$conversionProfileId = $partner->getDefaultConversionProfileId();
		}
			
		$vmcVersion = $partner->getVmcVersion();
		$check = null;
		if($vmcVersion < 2)
		{
			$check = ConversionProfilePeer::retrieveByPK($conversionProfileId);
		}
		else
		{
			$check = conversionProfile2Peer::retrieveByPK($conversionProfileId);
		}
		if(!$check)
			throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId);
		
		$objectData = new vBulkUploadEntryData();
		$objectData->setConversionProfileId($conversionProfileId);
		$data->setObjectData($objectData);
		
		return $data;
	}
	
	/**
	 * Get bulk upload batch job by id
	 *
	 * @action get
	 * @param bigint $id
	 * @return VidiunBulkUpload
	 */
	public function getAction($id)
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
	 * @param VidiunFilterPager $pager
	 * @return VidiunBulkUploadListResponse
	 */
	public function listAction(VidiunFilterPager $pager = null)
	{
	    if (!$pager)
			$pager = new VidiunFilterPager();
			
	    $c = new Criteria();
		$c->addAnd(BatchJobLogPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobLogPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		
		$crit = $c->getNewCriterion(BatchJobLogPeer::ABORT, null);
		$critOr = $c->getNewCriterion(BatchJobLogPeer::ABORT, 0);
		$crit->addOr($critOr);
		$c->add($crit);
		
		$c->addDescendingOrderByColumn(BatchJobLogPeer::ID);
		
		$count = BatchJobLogPeer::doCount($c);
		$pager->attachToCriteria($c);
		$jobs = BatchJobLogPeer::doSelect($c);
		
		$response = new VidiunBulkUploadListResponse();
		$response->objects = VidiunBulkUploads::fromBatchJobArray($jobs);
		$response->totalCount = $count; 
		
		return $response;
	}


	/**
	 * serve action return the original file.
	 * @action serve
	 * @param bigint $id job id
	 * @return file
	 * @throws VidiunAPIException
	 */
	public function serveAction($id)
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
		
		if (!$fileSync) {
			throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST, $id);
		}
		
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
	 * serveLog action return the original file.
	 * 
	 * @action serveLog
	 * @param bigint $id job id
	 * @return file
	 * 
	 */
	public function serveLogAction($id)
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
	 * @param bigint $id job id
	 * @return VidiunBulkUpload
	 */
	public function abortAction($id)
	{
		$c = new Criteria();
		$c->addAnd(BatchJobPeer::ID, $id);
		$c->addAnd(BatchJobPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		$batchJob = BatchJobPeer::doSelectOne($c);

		if (!$batchJob) {
			$c = new Criteria();
			$c->addAnd(BatchJobLogPeer::JOB_ID, $id);
			$c->addAnd(BatchJobLogPeer::PARTNER_ID, $this->getPartnerId());
			$c->addAnd(BatchJobLogPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);

			$crit = $c->getNewCriterion(BatchJobLogPeer::ABORT, null);
			$critOr = $c->getNewCriterion(BatchJobLogPeer::ABORT, 0);
			$crit->addOr($critOr);
			$c->add($crit);

			$batchJobLog = BatchJobLogPeer::doSelectOne($c);

			if(!$batchJobLog)
				throw new VidiunAPIException(VidiunErrors::BULK_UPLOAD_NOT_FOUND, $id);

			$batchJobLog->setAbort(BatchJobExecutionStatus::ABORTED);
			$batchJobLog->save();
		}
		else {
			vJobsManager::abortJob($id, BatchJobType::BULKUPLOAD, true);
		}

		$batchJobLog = BatchJobLogPeer::retrieveByBatchJobId($id);
		$ret = new VidiunBulkUpload();
		$ret->fromObject($batchJobLog, $this->getResponseProfile());
		return $ret;
	}
}

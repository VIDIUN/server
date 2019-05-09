<?php
/**
 * batch service lets you handle different batch process from remote machines.
 * As opposed to other objects in the system, locking mechanism is critical in this case.
 * For this reason the GetExclusiveXX, UpdateExclusiveXX and FreeExclusiveXX actions are important for the system's integrity.
 * In general - updating batch object should be done only using the UpdateExclusiveXX which in turn can be called only after 
 * acuiring a batch objet properly (using  GetExclusiveXX).
 * If an object was aquired and should be returned to the pool in it's initial state - use the FreeExclusiveXX action 
 *
 *	Terminology:
 *		LocationId
 *		ServerID
 *		ParternGroups 
 * 
 * @service jobs
 * @package api
 * @subpackage services
 */
class JobsService extends VidiunBaseService 
{
	// use initService to add a peer to the partner filter
	/**
	 * @ignore
	 */
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		if($this->getPartnerId() != Partner::ADMIN_CONSOLE_PARTNER_ID)
			$this->applyPartnerFilterForClass('BatchJob'); 	
	}
	
	
// --------------------------------- ImportJob functions 	--------------------------------- //
	
	
	/**
	 * batch getImportStatusAction returns the status of import task
	 * 
	 * @action getImportStatus
	 * @param int $jobId the id of the import job  
	 * @return VidiunBatchJobResponse 
	 */
	function getImportStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::IMPORT);
	}
	
	
	/**
	 * batch deleteImportAction deletes and returns the status of import task
	 * 
	 * @action deleteImport
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteImportAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::IMPORT);
	}
	
	
	/**
	 * batch abortImportAction aborts and returns the status of import task
	 * 
	 * @action abortImport
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortImportAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::IMPORT);
	}
	
	
	/**
	 * batch retryImportAction retries and returns the status of import task
	 * 
	 * @action retryImport
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryImportAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::IMPORT);
	}
	
	/**
// --------------------------------- ImportJob functions 	--------------------------------- //

	
	
	
// --------------------------------- ProvisionProvideJob functions 	--------------------------------- //
	
	
	/**
	 * batch getProvisionProvideStatusAction returns the status of ProvisionProvide task
	 * 
	 * @action getProvisionProvideStatus
	 * @param int $jobId the id of the ProvisionProvide job  
	 * @return VidiunBatchJobResponse 
	 */
	function getProvisionProvideStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::PROVISION_PROVIDE);
	}
	
	
	/**
	 * batch deleteProvisionProvideAction deletes and returns the status of ProvisionProvide task
	 * 
	 * @action deleteProvisionProvide
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteProvisionProvideAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::PROVISION_PROVIDE);
	}
	
	
	/**
	 * batch abortProvisionProvideAction aborts and returns the status of ProvisionProvide task
	 * 
	 * @action abortProvisionProvide
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortProvisionProvideAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::PROVISION_PROVIDE);
	}
	
	
	/**
	 * batch retryProvisionProvideAction retries and returns the status of ProvisionProvide task
	 * 
	 * @action retryProvisionProvide
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryProvisionProvideAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::PROVISION_PROVIDE);
	}
	
	/**
// --------------------------------- ProvisionProvideJob functions 	--------------------------------- //

	
	
// --------------------------------- ProvisionDeleteJob functions 	--------------------------------- //
	
	
	/**
	 * batch getProvisionDeleteStatusAction returns the status of ProvisionDelete task
	 * 
	 * @action getProvisionDeleteStatus
	 * @param int $jobId the id of the ProvisionDelete job  
	 * @return VidiunBatchJobResponse 
	 */
	function getProvisionDeleteStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::PROVISION_DELETE);
	}
	
	
	/**
	 * batch deleteProvisionDeleteAction deletes and returns the status of ProvisionDelete task
	 * 
	 * @action deleteProvisionDelete
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteProvisionDeleteAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::PROVISION_DELETE);
	}
	
	
	/**
	 * batch abortProvisionDeleteAction aborts and returns the status of ProvisionDelete task
	 * 
	 * @action abortProvisionDelete
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortProvisionDeleteAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::PROVISION_DELETE);
	}
	
	
	/**
	 * batch retryProvisionDeleteAction retries and returns the status of ProvisionDelete task
	 * 
	 * @action retryProvisionDelete
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryProvisionDeleteAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::PROVISION_DELETE);
	}
	
	/**
// --------------------------------- ProvisionDeleteJob functions 	--------------------------------- //

	
	
// --------------------------------- BulkUploadJob functions 	--------------------------------- //
	
	
	/**
	 * batch getBulkUploadStatusAction returns the status of bulk upload task
	 * 
	 * @action getBulkUploadStatus
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function getBulkUploadStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::BULKUPLOAD);
	}
	
	
	/**
	 * batch deleteBulkUploadAction deletes and returns the status of bulk upload task
	 * 
	 * @action deleteBulkUpload
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteBulkUploadAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::BULKUPLOAD);
	}
	
	
	/**
	 * batch abortBulkUploadAction aborts and returns the status of bulk upload task
	 * 
	 * @action abortBulkUpload
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortBulkUploadAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::BULKUPLOAD);
	}
	
	
	/**
	 * batch retryBulkUploadAction retries and returns the status of bulk upload task
	 * 
	 * @action retryBulkUpload
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryBulkUploadAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::BULKUPLOAD);
	}
	

	
// --------------------------------- BulkUploadJob functions 	--------------------------------- //

	
	
// --------------------------------- ConvertJob functions 	--------------------------------- //

	
	
	/**
	 * batch getConvertStatusAction returns the status of convert task
	 * 
	 * @action getConvertStatus
	 * @param int $jobId the id of the convert job  
	 * @return VidiunBatchJobResponse 
	 */
	function getConvertStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::CONVERT);
	}
	
	
	
	/**
	 * batch getConvertCollectionStatusAction returns the status of convert task
	 * 
	 * @action getConvertCollectionStatus
	 * @param int $jobId the id of the convert profile job  
	 * @return VidiunBatchJobResponse 
	 */
	function getConvertCollectionStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::CONVERT_COLLECTION);
	}
	
	
	
	/**
	 * batch getConvertProfileStatusAction returns the status of convert task
	 * 
	 * @action getConvertProfileStatus
	 * @param int $jobId the id of the convert profile job  
	 * @return VidiunBatchJobResponse 
	 */
	function getConvertProfileStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::CONVERT_PROFILE);
	}
	
	
	
	/**
	 * batch addConvertProfileJobAction creates a new convert profile job
	 * 
	 * @action addConvertProfileJob
	 * @param string $entryId the id of the entry to be reconverted  
	 * @return VidiunBatchJobResponse 
	 */
	function addConvertProfileJobAction($entryId)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if(!$entry)
			throw new VidiunAPIException(APIErrors::INVALID_ENTRY_ID, 'entry', $entryId);
			
		$flavorAsset = assetPeer::retrieveOriginalByEntryId($entryId);
		if(!$flavorAsset)
			throw new VidiunAPIException(VidiunErrors::ORIGINAL_FLAVOR_ASSET_IS_MISSING);
		
		$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		if(!vFileSyncUtils::file_exists($syncKey, true))
			throw new VidiunAPIException(APIErrors::NO_FILES_RECEIVED);

		$fileSync = $fileSync = vFileSyncUtils::getLocalFileSyncForKey($syncKey, false);
		$batchJob = vJobsManager::addConvertProfileJob(null, $entry, $flavorAsset->getId(), $fileSync);
		if(!$batchJob)
			throw new VidiunAPIException(APIErrors::UNABLE_TO_CONVERT_ENTRY);
		
		return $this->getStatusAction($batchJob->getId(), VidiunBatchJobType::CONVERT_PROFILE);
	}
	
	
	/**
	 * batch deleteConvertAction deletes and returns the status of convert task
	 * 
	 * @action deleteConvert
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteConvertAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::CONVERT);
	}

	
	/**
	 * batch abortConvertAction aborts and returns the status of convert task
	 * 
	 * @action abortConvert
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortConvertAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::CONVERT);
	}

	
	/**
	 * batch retryConvertAction retries and returns the status of convert task
	 * 
	 * @action retryConvert
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryConvertAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::CONVERT);
	}

	
	/**
	 * batch deleteConvertCollectionAction deletes and returns the status of convert profile task
	 * 
	 * @action deleteConvertCollection
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteConvertCollectionAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::CONVERT_COLLECTION);
	}

	
	/**
	 * batch deleteConvertProfileAction deletes and returns the status of convert profile task
	 * 
	 * @action deleteConvertProfile
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteConvertProfileAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::CONVERT_PROFILE);
	}

	
	/**
	 * batch abortConvertCollectionAction aborts and returns the status of convert profile task
	 * 
	 * @action abortConvertCollection
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortConvertCollectionAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::CONVERT_COLLECTION);
	}

	
	/**
	 * batch abortConvertProfileAction aborts and returns the status of convert profile task
	 * 
	 * @action abortConvertProfile
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortConvertProfileAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::CONVERT_PROFILE);
	}

	
	/**
	 * batch retryConvertCollectionAction retries and returns the status of convert profile task
	 * 
	 * @action retryConvertCollection
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryConvertCollectionAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::CONVERT_COLLECTION);
	}

	
	/**
	 * batch retryConvertProfileAction retries and returns the status of convert profile task
	 * 
	 * @action retryConvertProfile
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryConvertProfileAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::CONVERT_PROFILE);
	}
	
// --------------------------------- ConvertJob functions 	--------------------------------- //

	
	
// --------------------------------- PostConvertJob functions 	--------------------------------- //

	
	/**
	 * batch getPostConvertStatusAction returns the status of post convert task
	 * 
	 * @action getPostConvertStatus
	 * @param int $jobId the id of the post convert job  
	 * @return VidiunBatchJobResponse 
	 */
	function getPostConvertStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::POSTCONVERT);
	}
	
	
	/**
	 * batch deletePostConvertAction deletes and returns the status of post convert task
	 * 
	 * @action deletePostConvert
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deletePostConvertAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::POSTCONVERT);
	}
	
	
	/**
	 * batch abortPostConvertAction aborts and returns the status of post convert task
	 * 
	 * @action abortPostConvert
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortPostConvertAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::POSTCONVERT);
	}
	
	
	/**
	 * batch retryPostConvertAction retries and returns the status of post convert task
	 * 
	 * @action retryPostConvert
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryPostConvertAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::POSTCONVERT);
	}
	

// --------------------------------- PostConvertJob functions 	--------------------------------- //

// --------------------------------- CaptureThumbJob functions 	--------------------------------- //

	
	/**
	 * batch getCaptureThumbStatusAction returns the status of capture thumbnail task
	 * 
	 * @action getCaptureThumbStatus
	 * @param int $jobId the id of the capture thumbnail job  
	 * @return VidiunBatchJobResponse 
	 */
	function getCaptureThumbStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::CAPTURE_THUMB);
	}
	
	
	/**
	 * batch deleteCaptureThumbAction deletes and returns the status of capture thumbnail task
	 * 
	 * @action deleteCaptureThumb
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteCaptureThumbAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::CAPTURE_THUMB);
	}
	
	
	/**
	 * batch abortCaptureThumbAction aborts and returns the status of capture thumbnail task
	 * 
	 * @action abortCaptureThumb
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortCaptureThumbAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::CAPTURE_THUMB);
	}
	
	
	/**
	 * batch retryCaptureThumbAction retries and returns the status of capture thumbnail task
	 * 
	 * @action retryCaptureThumb
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryCaptureThumbAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::CAPTURE_THUMB);
	}
	

// --------------------------------- CaptureThumbJob functions 	--------------------------------- //
	
	
// --------------------------------- ExtractMediaJob functions 	--------------------------------- //
	
	
	/**
	 * batch getExtractMediaStatusAction returns the status of extract media task
	 * 
	 * @action getExtractMediaStatus
	 * @param int $jobId the id of the extract media job  
	 * @return VidiunBatchJobResponse 
	 */
	function getExtractMediaStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::EXTRACT_MEDIA);
	}
	
	
	/**
	 * batch deleteExtractMediaAction deletes and returns the status of extract media task
	 * 
	 * @action deleteExtractMedia
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteExtractMediaAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::EXTRACT_MEDIA);
	}
	
	
	/**
	 * batch abortExtractMediaAction aborts and returns the status of extract media task
	 * 
	 * @action abortExtractMedia
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortExtractMediaAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::EXTRACT_MEDIA);
	}
	
	
	/**
	 * batch retryExtractMediaAction retries and returns the status of extract media task
	 * 
	 * @action retryExtractMedia
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryExtractMediaAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::EXTRACT_MEDIA);
	}
	

	
	
// --------------------------------- ExtractMediaJob functions 	--------------------------------- //
	
// --------------------------------- StorageExportJob functions 	--------------------------------- //
	
	
	/**
	 * batch getStorageExportStatusAction returns the status of export task
	 * 
	 * @action getStorageExportStatus
	 * @param int $jobId the id of the export job  
	 * @return VidiunBatchJobResponse 
	 */
	function getStorageExportStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::STORAGE_EXPORT);
	}
	
	
	/**
	 * batch deleteStorageExportAction deletes and returns the status of export task
	 * 
	 * @action deleteStorageExport
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteStorageExportAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::STORAGE_EXPORT);
	}
	
	
	/**
	 * batch abortStorageExportAction aborts and returns the status of export task
	 * 
	 * @action abortStorageExport
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortStorageExportAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::STORAGE_EXPORT);
	}
	
	
	/**
	 * batch retryStorageExportAction retries and returns the status of export task
	 * 
	 * @action retryStorageExport
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryStorageExportAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::STORAGE_EXPORT);
	}
	

	
	
// --------------------------------- StorageExportJob functions 	--------------------------------- //
	
// --------------------------------- StorageDeleteJob functions 	--------------------------------- //
	
	
	/**
	 * batch getStorageDeleteStatusAction returns the status of export task
	 * 
	 * @action getStorageDeleteStatus
	 * @param int $jobId the id of the export job  
	 * @return VidiunBatchJobResponse 
	 */
	function getStorageDeleteStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::STORAGE_DELETE);
	}
	
	
	/**
	 * batch deleteStorageDeleteAction deletes and returns the status of export task
	 * 
	 * @action deleteStorageDelete
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteStorageDeleteAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::STORAGE_DELETE);
	}
	
	
	/**
	 * batch abortStorageDeleteAction aborts and returns the status of export task
	 * 
	 * @action abortStorageDelete
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortStorageDeleteAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::STORAGE_DELETE);
	}
	
	
	/**
	 * batch retryStorageDeleteAction retries and returns the status of export task
	 * 
	 * @action retryStorageDelete
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryStorageDeleteAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::STORAGE_DELETE);
	}
	

	
	
// --------------------------------- StorageDeleteJob functions 	--------------------------------- //
	
// --------------------------------- ImportJob functions 	--------------------------------- //
	
	/**
	 * batch getNotificationStatusAction returns the status of Notification task
	 * 
	 * @action getNotificationStatus
	 * @param int $jobId the id of the Notification job  
	 * @return VidiunBatchJobResponse 
	 */
	function getNotificationStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::NOTIFICATION);
	}
	
	
	/**
	 * batch deleteNotificationAction deletes and returns the status of notification task
	 * 
	 * @action deleteNotification
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteNotificationAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::NOTIFICATION);
	}
	
	
	/**
	 * batch abortNotificationAction aborts and returns the status of notification task
	 * 
	 * @action abortNotification
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortNotificationAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::NOTIFICATION);
	}
	
	
	/**
	 * batch retryNotificationAction retries and returns the status of notification task
	 * 
	 * @action retryNotification
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryNotificationAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::NOTIFICATION);
	}
	
	
// --------------------------------- Notification functions 	--------------------------------- //


	
// --------------------------------- MailJob functions 	--------------------------------- //	
	
	
	/**
	 * batch getMailStatusAction returns the status of mail task
	 * 
	 * @action getMailStatus
	 * @param int $jobId the id of the mail job  
	 * @return VidiunBatchJobResponse 
	 */
	function getMailStatusAction($jobId)
	{
		return $this->getStatusAction($jobId, VidiunBatchJobType::MAIL);
	}
	
	
	/**
	 * batch deleteMailAction deletes and returns the status of mail task
	 * 
	 * @action deleteMail
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteMailAction($jobId)
	{
		return $this->deleteJobAction($jobId, VidiunBatchJobType::MAIL);
	}
	
	
	/**
	 * batch abortMailAction aborts and returns the status of mail task
	 * 
	 * @action abortMail
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortMailAction($jobId)
	{
		return $this->abortJobAction($jobId, VidiunBatchJobType::MAIL);
	}
	
	
	/**
	 * batch retryMailAction retries and returns the status of mail task
	 * 
	 * @action retryMail
	 * @param int $jobId the id of the bulk upload job  
	 * @return VidiunBatchJobResponse 
	 */
	function retryMailAction($jobId)
	{
		return $this->retryJobAction($jobId, VidiunBatchJobType::MAIL);
	}
	
	/**
	 * Adds new mail job
	 * 
	 * @action addMailJob
	 * @param VidiunMailJobData $mailJobData
	 */
	function addMailJobAction(VidiunMailJobData $mailJobData)
	{
		$mailJobData->validatePropertyNotNull("mailType");
		$mailJobData->validatePropertyNotNull("recipientEmail");
		
		if (is_null($mailJobData->mailPriority))
			$mailJobData->mailPriority = vMailJobData::MAIL_PRIORITY_NORMAL;
			
		if (is_null($mailJobData->fromEmail))
			$mailJobData->fromEmail = vConf::get("default_email");

		if (is_null($mailJobData->fromName))
			$mailJobData->fromName = vConf::get("default_email_name");
			
		$batchJob = new BatchJob();
		$batchJob->setPartnerId($this->getPartnerId());
		
		$mailJobDataDb = $mailJobData->toObject(new vMailJobData());
			
		vJobsManager::addJob($batchJob, $mailJobDataDb, BatchJobType::MAIL, $mailJobDataDb->getMailType());
	}
	
// --------------------------------- MailJob functions 	--------------------------------- //
	
		
// --------------------------------- generic functions 	--------------------------------- //
	
	
	/**
	 * batch addBatchJob action allows to add a generic BatchJob 
	 * 
	 * @action addBatchJob
	 * @param VidiunBatchJob $batchJob  
	 * @return VidiunBatchJob 
	 */
	function addBatchJobAction(VidiunBatchJob $batchJob)
	{
		vJobsManager::addJob($batchJob->toObject(), $batchJob->data, $batchJob->jobType, $batchJob->jobSubType);	
	}

	
	
	/**
	 * batch getStatusAction returns the status of task
	 * 
	 * @action getStatus
	 * @param int $jobId the id of the job  
	 * @param VidiunBatchJobType $jobType the type of the job
	 * @param VidiunFilterPager $pager pager for the child jobs  
	 * @return VidiunBatchJobResponse 
	 */
	function getStatusAction($jobId, $jobType, VidiunFilterPager $pager = null)
	{
		$dbJobType = vPluginableEnumsManager::apiToCore('BatchJobType', $jobType);
		
		$dbBatchJob = BatchJobPeer::retrieveByPK($jobId);
		if($dbBatchJob->getJobType() != $dbJobType)
			throw new VidiunAPIException(APIErrors::GET_EXCLUSIVE_JOB_WRONG_TYPE, $jobType, $dbBatchJob->getId());
		
		$dbBatchJobLock = BatchJobLockPeer::retrieveByPK($jobId);
		
		$job = new VidiunBatchJob();
		$job->fromBatchJob($dbBatchJob,$dbBatchJobLock);
		
		$batchJobResponse = new VidiunBatchJobResponse();
		$batchJobResponse->batchJob = $job;
		
		if(!$pager)
			$pager = new VidiunFilterPager();
			
		$c = new Criteria();
		$pager->attachToCriteria($c);
		
		$childBatchJobs = $dbBatchJob->getChildJobs($c);
		$batchJobResponse->childBatchJobs = VidiunBatchJobArray::fromBatchJobArray($childBatchJobs);
		
		return $batchJobResponse;
	}

	
	
	/**
	 * batch deleteJobAction deletes and returns the status of task
	 * 
	 * @action deleteJob
	 * @param int $jobId the id of the job  
	 * @param VidiunBatchJobType $jobType the type of the job  
	 * @return VidiunBatchJobResponse 
	 */
	function deleteJobAction($jobId, $jobType)
	{
		$dbJobType = vPluginableEnumsManager::apiToCore('BatchJobType', $jobType);
		vJobsManager::deleteJob($jobId, $dbJobType);
		return $this->getStatusAction($jobId, $jobType);
	}

	
	
	/**
	 * batch abortJobAction aborts and returns the status of task
	 * 
	 * @action abortJob
	 * @param int $jobId the id of the job  
	 * @param VidiunBatchJobType $jobType the type of the job  
	 * @return VidiunBatchJobResponse 
	 */
	function abortJobAction($jobId, $jobType)
	{
		$dbJobType = vPluginableEnumsManager::apiToCore('BatchJobType', $jobType);
		vJobsManager::abortJob($jobId, $dbJobType);
		return $this->getStatusAction($jobId, $jobType);
	}

	
	
	/**
	 * batch retryJobAction aborts and returns the status of task
	 * 
	 * @action retryJob
	 * @param int $jobId the id of the job  
	 * @param VidiunBatchJobType $jobType the type of the job  
	 * @param bool $force should we force the restart. 
	 * @return VidiunBatchJobResponse 
	 */
	function retryJobAction($jobId, $jobType, $force = false)
	{
		$dbJobType = vPluginableEnumsManager::apiToCore('BatchJobType', $jobType);
		vJobsManager::retryJob($jobId, $dbJobType, $force);
		return $this->getStatusAction($jobId, $jobType);
	}
	
	/**
	 * batch boostEntryJobsAction boosts all the jobs associated with the entry
	 * 
	 * @action boostEntryJobs
	 * @param string $entryId the id of the entry to be boosted  
	 */
	function boostEntryJobsAction($entryId)
	{
		vJobsManager::boostEntryJobs($entryId);
	}

	/**
	 * list Batch Jobs 
	 * 
	 * @action listBatchJobs
	 * @param VidiunBatchJobFilter $filter
	 * @param VidiunFilterPager $pager  
	 * @return VidiunBatchJobListResponse
	 */
	function listBatchJobsAction(VidiunBatchJobFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter) 
			$filter = new VidiunBatchJobFilter();
			
		$batchJobFilter = new BatchJobFilter (true);
		$filter->toObject($batchJobFilter);
		
		$c = new Criteria();
//		$c->add(BatchJobPeer::DELETED_AT, null);
		
		$batchJobFilter->attachToCriteria($c);
		
		if(!$pager)
		   $pager = new VidiunFilterPager();
		
		$pager->attachToCriteria($c);
		
		myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;
		
		$list = BatchJobPeer::doSelect($c);
		
		$c->setLimit(false);
		$count = BatchJobPeer::doCount($c);

		$newList = VidiunBatchJobArray::fromStatisticsBatchJobArray($list );
		
		$response = new VidiunBatchJobListResponse();
		$response->objects = $newList;
		$response->totalCount = $count;
		
		return $response;
	}
	
// --------------------------------- generic functions 	--------------------------------- //	
	
	
	
}

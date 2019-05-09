<?php

/**
 *
 * Manages the batch flow
 *
 * @package Core
 * @subpackage Batch
 *
 */
class vFlowManager implements vBatchJobStatusEventConsumer, vObjectAddedEventConsumer, vObjectChangedEventConsumer, vObjectDeletedEventConsumer, vObjectReadyForReplacmentEventConsumer,vObjectDataChangedEventConsumer
{
	public final function __construct()
	{
	}

	protected function updatedImport(BatchJob $dbBatchJob, vImportJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleImportFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_RETRY:
				return vFlowHelper::handleImportRetried($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleImportFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedConcat(BatchJob $dbBatchJob, vConcatJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleConcatFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleConcatFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedConvertLiveSegment(BatchJob $dbBatchJob, vConvertLiveSegmentJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleConvertLiveSegmentFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleConvertLiveSegmentFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedIndex(BatchJob $dbBatchJob, kIndexJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_PENDING:
				return vFlowHelper::handleIndexPending($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleIndexFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleIndexFailed($dbBatchJob, $data);
				return $dbBatchJob;
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedCopy(BatchJob $dbBatchJob, vCopyJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
//				return vFlowHelper::handleCopyFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
//				return vFlowHelper::handleCopyFailed($dbBatchJob, $data);
				return $dbBatchJob;
			default:
				return $dbBatchJob;
		}
	}
	
	protected function updatedDelete(BatchJob $dbBatchJob, vDeleteJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				//				return vFlowHelper::handleDeleteFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				//				return vFlowHelper::handleDeleteFailed($dbBatchJob, $data);
				return $dbBatchJob;
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedExtractMedia(BatchJob $dbBatchJob, vExtractMediaJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleExtractMediaClosed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedMoveCategoryEntries(BatchJob $dbBatchJob, vMoveCategoryEntriesJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
//				return vFlowHelper::handleMoveCategoryEntriesFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
//				return vFlowHelper::handleMoveCategoryEntriesFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedStorageExport(BatchJob $dbBatchJob, vStorageExportJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleStorageExportFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleStorageExportFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedStorageDelete(BatchJob $dbBatchJob, vStorageDeleteJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleStorageDeleteFinished($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedCaptureThumb(BatchJob $dbBatchJob, vCaptureThumbJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleCaptureThumbFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleCaptureThumbFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}
	
	protected function updatedDeleteFile (BatchJob $dbBatchJob, vDeleteFileJobData $data)
	{
		switch ($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_PROCESSING:
				vFlowHelper::handleDeleteFileProcessing($data);
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleDeleteFileFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
			default:
				return $dbBatchJob;
		}	
	}

	protected function updatedConvert(BatchJob $dbBatchJob, vConvertJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_PENDING:
				return vFlowHelper::handleConvertPending($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_QUEUED:
				return vFlowHelper::handleConvertQueued($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleConvertFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleConvertFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedPostConvert(BatchJob $dbBatchJob, vPostConvertJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handlePostConvertFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handlePostConvertFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedBulkUpload(BatchJob $dbBatchJob, vBulkUploadJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FAILED: 
			case BatchJob::BATCHJOB_STATUS_FATAL: 
				return vFlowHelper::handleBulkUploadFailed($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FINISHED: 
				return vFlowHelper::handleBulkUploadFinished($dbBatchJob, $data);
			default: return $dbBatchJob;
		}
	}

	protected function updatedConvertCollection(BatchJob $dbBatchJob, vConvertCollectionJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_PENDING:
				return vFlowHelper::handleConvertCollectionPending($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleConvertCollectionFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleConvertCollectionFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedConvertProfile(BatchJob $dbBatchJob, vConvertProfileJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_PENDING:
				return vFlowHelper::handleConvertProfilePending($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleConvertProfileFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleConvertProfileFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedBulkDownload(BatchJob $dbBatchJob, vBulkDownloadJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_PENDING:
				return vFlowHelper::handleBulkDownloadPending($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleBulkDownloadFinished($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedProvisionDelete(BatchJob $dbBatchJob, vProvisionJobData $data)
	{
		return $dbBatchJob;
	}

	protected function updatedProvisionProvide(BatchJob $dbBatchJob, vProvisionJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleProvisionProvideFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleProvisionProvideFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}
	
	protected function updatedLiveReportExport(BatchJob $dbBatchJob, vLiveReportExportJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleLiveReportExportFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleLiveReportExportFailed($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_ABORTED:
				return vFlowHelper::handleLiveReportExportAborted($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	protected function updatedReportExport(BatchJob $dbBatchJob, vReportExportJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleReportExportFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return vFlowHelper::handleReportExportFailed($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_ABORTED:
				return vFlowHelper::handleReportExportAborted($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{ 
		$dbBatchJobLock = $dbBatchJob->getBatchJobLock();
		
		try
		{
			if($dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_FAILED || $dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_FATAL)	{
				vJobsManager::abortChildJobs($dbBatchJob);
			}
			
			$jobType = $dbBatchJob->getJobType();
			switch($jobType)
			{
				case BatchJobType::IMPORT:
					$dbBatchJob = $this->updatedImport($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::EXTRACT_MEDIA:
					$dbBatchJob = $this->updatedExtractMedia($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::CONVERT:
					$dbBatchJob = $this->updatedConvert($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::POSTCONVERT:
					$dbBatchJob = $this->updatedPostConvert($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::BULKUPLOAD:
					$dbBatchJob = $this->updatedBulkUpload($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::CONVERT_PROFILE:
					$dbBatchJob = $this->updatedConvertProfile($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::BULKDOWNLOAD:
					$dbBatchJob = $this->updatedBulkDownload($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::PROVISION_PROVIDE:
					$dbBatchJob = $this->updatedProvisionProvide($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::PROVISION_DELETE:
					$dbBatchJob = $this->updatedProvisionDelete($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::CONVERT_COLLECTION:
					$dbBatchJob = $this->updatedConvertCollection($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::STORAGE_EXPORT:
					$dbBatchJob = $this->updatedStorageExport($dbBatchJob, $dbBatchJob->getData());
					break;
					
				case BatchJobType::MOVE_CATEGORY_ENTRIES:
					$dbBatchJob = $this->updatedMoveCategoryEntries($dbBatchJob, $dbBatchJob->getData());
					break;
							
				case BatchJobType::STORAGE_DELETE:
					$dbBatchJob = $this->updatedStorageDelete($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::CAPTURE_THUMB:
					$dbBatchJob = $this->updatedCaptureThumb($dbBatchJob, $dbBatchJob->getData());
					break;
					
				case BatchJobType::DELETE_FILE:
					$dbBatchJob=$this->updatedDeleteFile($dbBatchJob, $dbBatchJob->getData());
					break;
					
				case BatchJobType::INDEX:
					$dbBatchJob=$this->updatedIndex($dbBatchJob, $dbBatchJob->getData());
					break;
					
				case BatchJobType::COPY:
					$dbBatchJob=$this->updatedCopy($dbBatchJob, $dbBatchJob->getData());
					break;
					
				case BatchJobType::DELETE:
					$dbBatchJob=$this->updatedDelete($dbBatchJob, $dbBatchJob->getData());
					break;
					
				case BatchJobType::CONCAT:
					$dbBatchJob=$this->updatedConcat($dbBatchJob, $dbBatchJob->getData());
					break;
					
				case BatchJobType::CONVERT_LIVE_SEGMENT:
					$dbBatchJob=$this->updatedConvertLiveSegment($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::LIVE_REPORT_EXPORT:
					$dbBatchJob=$this->updatedLiveReportExport($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::EXPORT_CSV:
					$dbBatchJob = $this->updatedExportCsv($dbBatchJob, $dbBatchJob->getData());
					break;

				case BatchJobType::REPORT_EXPORT:
					$dbBatchJob = $this->updatedReportExport($dbBatchJob, $dbBatchJob->getData());
					break;

				default:
					break;
			}
			
			if($dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_RETRY) {
				
				if($dbBatchJobLock && $dbBatchJobLock->getExecutionAttempts() >= BatchJobLockPeer::getMaxExecutionAttempts($jobType))
					$dbBatchJob = vJobsManager::updateBatchJob($dbBatchJob, BatchJob::BATCHJOB_STATUS_FAILED);
			}
			
			if(in_array($dbBatchJob->getStatus(), BatchJobPeer::getClosedStatusList()))
			{
				$jobEntry = $dbBatchJob->getEntry();
				if($jobEntry && $jobEntry->getMarkedForDeletion())
					myEntryUtils::deleteEntry($jobEntry,null,true);
			}
		}
		catch ( Exception $ex )
		{
			self::alert($dbBatchJob, $ex);
			VidiunLog::err( "Error:" . $ex->getMessage() );
		}
			
		return true;
	}

	// creates a mail job with the exception data
	protected static function alert(BatchJob $dbBatchJob, Exception $exception)
	{
		$jobData = new vMailJobData();
		$jobData->setMailPriority( vMailJobData::MAIL_PRIORITY_HIGH);
		$jobData->setStatus(vMailJobData::MAIL_STATUS_PENDING);

		VidiunLog::alert("Error in job [{$dbBatchJob->getId()}]\n".$exception);

		$jobData->setMailType(90); // is the email template
		$jobData->setBodyParamsArray(array($dbBatchJob->getId(), $exception->getFile(), $exception->getLine(), $exception->getMessage(), $exception->getTraceAsString()));

		$jobData->setFromEmail(vConf::get("batch_alert_email"));
		$jobData->setFromName(vConf::get("batch_alert_name"));
		$jobData->setRecipientEmail(vConf::get("batch_alert_email"));
		$jobData->setSubjectParamsArray( array() );

		vJobsManager::addJob($dbBatchJob->createChild(BatchJobType::MAIL, $jobData->getMailType()), $jobData, BatchJobType::MAIL, $jobData->getMailType());
	}

	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::shouldConsumeAddedEvent()
	 */
	public function shouldConsumeAddedEvent(BaseObject $object)
	{
		if($object instanceof asset)
			return true;

		return false;
	}

	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::objectAdded()
	 */
	public function objectAdded(BaseObject $object, BatchJob $raisedJob = null)
	{
		/** @var entry $entry */
		$entry = $object->getentry();

		if ($object->getStatus() == asset::FLAVOR_ASSET_STATUS_QUEUED || $object->getStatus() == asset::FLAVOR_ASSET_STATUS_IMPORTING)
		{
			if (!($object instanceof flavorAsset))
			{
				$object->setStatus(asset::FLAVOR_ASSET_STATUS_READY);
				$object->save();
			} elseif ($object->getIsOriginal())
			{
				if ($entry->getType() == entryType::MEDIA_CLIP)
				{
					if ($entry->getFlowType() == EntryFlowType::IMPORT_FOR_CLIP_CONCAT)
					{
						$object->setStatus(asset::FLAVOR_ASSET_STATUS_READY);
						$object->save();
						return true;
					}
					$allowedFlows = array(EntryFlowType::CLIP_CONCAT, EntryFlowType::TRIM_CONCAT);
					if ($entry->getOperationAttributes() && $object->getIsOriginal() && !in_array($entry->getFlowType(), $allowedFlows))
						vBusinessPreConvertDL::convertSource($object, null, null, $raisedJob);
					else
					{
						$syncKey = $object->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);

						if (vFileSyncUtils::fileSync_exists($syncKey))
						{
							list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
							vJobsManager::addConvertProfileJob($raisedJob, $entry, $object->getId(), $fileSync);
						}
					}

				}
			} else
			{
				$object->setStatus(asset::FLAVOR_ASSET_STATUS_VALIDATING);
				$object->save();
			}
		}

		if ($object->getStatus() == asset::FLAVOR_ASSET_STATUS_READY && $object instanceof thumbAsset)
		{
			if ($object->getFlavorParamsId())
				vFlowHelper::generateThumbnailsFromFlavor($object->getEntryId(), $raisedJob, $object->getFlavorParamsId());
			else
				if ($object->hasTag(thumbParams::TAG_DEFAULT_THUMB))
					vBusinessConvertDL::setAsDefaultThumbAsset($object);
			return true;
		}


		if ($object->getIsOriginal() && $entry->getStatus() == entryStatus::NO_CONTENT)
		{
			$entry->setStatus(entryStatus::PENDING);
			$entry->save();
		}

		return true;
	}

	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::shouldConsumeChangedEvent()
	 */
	public function shouldConsumeChangedEvent(BaseObject $object, array $modifiedColumns)
	{
		if(
			$object instanceof entry
			&&	in_array(entryPeer::STATUS, $modifiedColumns)
			&&	($object->getStatus() == entryStatus::READY || $object->getStatus() == entryStatus::ERROR_CONVERTING)
			&&	$object->getReplacedEntryId()
		)
			return true;

		if(
			$object instanceof UploadToken
			&&	in_array(UploadTokenPeer::STATUS, $modifiedColumns)
			&&	$object->getStatus() == UploadToken::UPLOAD_TOKEN_FULL_UPLOAD
		)
			return true;


		if(
			$object instanceof ClippingTaskEntryServerNode
			&&	in_array(EntryServerNodePeer::STATUS, $modifiedColumns)
		)
			return true;


		if(
			$object instanceof flavorAsset
			&&	in_array(assetPeer::STATUS, $modifiedColumns)
		)
			return true;
			
		if(
			$object instanceof BatchJob
			&&	$object->getJobType() == BatchJobType::BULKUPLOAD
			&&	$object->getStatus() == BatchJob::BATCHJOB_STATUS_ABORTED
			&&	in_array(BatchJobPeer::STATUS, $modifiedColumns)
			&&	in_array($object->getColumnsOldValue(BatchJobPeer::STATUS), BatchJobPeer::getClosedStatusList())
		)
			return true;
			
			
		if ($object instanceof UserRole
			&& in_array(UserRolePeer::PERMISSION_NAMES, $modifiedColumns))
			{
				return true;
			}

		return false;
	}

	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::objectChanged()
	 */
	public function objectChanged(BaseObject $object, array $modifiedColumns)
	{
		if(
			$object instanceof entry
			&&	in_array(entryPeer::STATUS, $modifiedColumns)
			&&	($object->getStatus() == entryStatus::READY || $object->getStatus() == entryStatus::ERROR_CONVERTING)
			&&	$object->getReplacedEntryId()
		)
		{
			vFlowHelper::handleEntryReplacement($object);
			return true;
		}

		if(
			$object instanceof UploadToken
			&&	in_array(UploadTokenPeer::STATUS, $modifiedColumns)
			&&	$object->getStatus() == UploadToken::UPLOAD_TOKEN_FULL_UPLOAD
		)
		{
			vFlowHelper::handleUploadFinished($object);
			return true;
		}
		
		if(
			$object instanceof ClippingTaskEntryServerNode
			&&	in_array(EntryServerNodePeer::STATUS, $modifiedColumns)
		)
		{
			if ($object->getServerType() == EntryServerNodeType::LIVE_CLIPPING_TASK)
				vFlowHelper::handleClippingTaskStatusUpdate($object);
			return true;
		}

		if(
			$object instanceof BatchJob
			&&	$object->getJobType() == BatchJobType::BULKUPLOAD
			&&	$object->getStatus() == BatchJob::BATCHJOB_STATUS_ABORTED
			&&	in_array(BatchJobPeer::STATUS, $modifiedColumns)
			&&	in_array($object->getColumnsOldValue(BatchJobPeer::STATUS), BatchJobPeer::getClosedStatusList())
		)
		{
			$partner = $object->getPartner();
			if($partner->getEnableBulkUploadNotificationsEmails())
				vFlowHelper::sendBulkUploadNotificationEmail($object, MailType::MAIL_TYPE_BULKUPLOAD_ABORTED, array($partner->getAdminName(), $object->getId(), vFlowHelper::createBulkUploadLogUrl($object)));
				
			return true;
		}
			
		if ($object instanceof UserRole
			&& in_array(UserRolePeer::PERMISSION_NAMES, $modifiedColumns))
		{
			$filter = new vuserFilter();
			$filter->set('_eq_role_ids', $object->getId());
			vJobsManager::addIndexJob($object->getPartnerId(), IndexObjectType::USER, $filter, false);
			return true;
		}

		if(
			!($object instanceof flavorAsset)
			||	!in_array(assetPeer::STATUS, $modifiedColumns)
		)
			return true;

			
		$entry = entryPeer::retrieveByPKNoFilter($object->getEntryId());

		VidiunLog::info("Asset id [" . $object->getId() . "] isOriginal [" . $object->getIsOriginal() . "] status [" . $object->getStatus() . "]");
		if($object->getIsOriginal())
			return true;
		
		if($object->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_VALIDATING)
		{
			$postConvertAssetType = BatchJob::POSTCONVERT_ASSET_TYPE_FLAVOR;
			$offset = $entry->getThumbOffset(); // entry getThumbOffset now takes the partner DefThumbOffset into consideration
			$syncKey = $object->getSyncKey(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);

			$fileSync = vFileSyncUtils::getLocalFileSyncForKey($syncKey, false);
			if(!$fileSync)
				return true;


			if(vFileSyncUtils::getLocalFilePathForKey($syncKey))
				vJobsManager::addPostConvertJob(null, $postConvertAssetType, $syncKey, $object->getId(), null, $entry->getCreateThumb(), $offset);

			$conversionProfile = $entry->getconversionProfile2();
			if($conversionProfile && !flavorParamsConversionProfilePeer::retrieveByConversionProfile( $entry->getConversionProfileId()) )
			{
				$conversionProfileTags = explode(',', $conversionProfile->getTags());
				if (in_array(conversionProfile2::SKIP_VALIDATION, $conversionProfileTags))
				{
					$object->setStatus(flavorAsset::ASSET_STATUS_READY);
					$object->save();
					$entry->setStatus(entryStatus::READY);
					$entry->save();
				}
			}
		}
		elseif ($object->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_READY)
		{
			// If we get a ready flavor and the entry is in no content
			if($entry->getStatus() == entryStatus::NO_CONTENT)
			{
				$entry->setStatus(entryStatus::PENDING); // we change the entry to pending
				$entry->save();
			}
		}

		return true;
	}

	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::shouldConsumeDeletedEvent()
	 */
	public function shouldConsumeDeletedEvent(BaseObject $object)
	{
		if($object instanceof UploadToken)
			return true;
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::shouldConsumeReadyForReplacmentEvent()
	 */
	public function shouldConsumeReadyForReplacmentEvent(BaseObject $object)
	{
		if($object instanceof entry)
			return true;
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::objectReadyForReplacment()
	 */
	public function objectReadyForReplacment(BaseObject $object, BatchJob $raisedJob = null)
	{
		
		$entry = entryPeer::retrieveByPK($object->getReplacedEntryId());
		if(!$entry)
		{
			VidiunLog::err("Real entry id [" . $object->getReplacedEntryId() . "] not found");
			return true;
		}
		
		vBusinessConvertDL::replaceEntry($entry, $object);
		return true;
	}
	

	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::objectDeleted()
	 */
	public function objectDeleted(BaseObject $object, BatchJob $raisedJob = null)
	{
		vFlowHelper::handleUploadCanceled($object);
		return true;
	}

	/**
	 * @param BaseObject $object
	 * @param string $previousVersion
	 * @return bool true if the consumer should handle the event
	 */
	public function shouldConsumeDataChangedEvent(BaseObject $object, $previousVersion = null)
	{
		if($object instanceof asset)
			return true;

		return false;		
	}

	/**
	 * @param BaseObject $object
	 * @param string $previousVersion
	 * @param BatchJob $raisedJob
	 * @return bool true if should continue to the next consumer
	 */
	public function objectDataChanged(BaseObject $object, $previousVersion = null, BatchJob $raisedJob = null)
	{
		if ($object instanceof flavorAsset)
		{
			if ($object->getStatus() == asset::FLAVOR_ASSET_STATUS_QUEUED)
			{
				if (!$object->getIsOriginal())
				{
					$object->setStatus(asset::FLAVOR_ASSET_STATUS_VALIDATING);
					$object->save();
				}
			}
		}
		return true;
	}
	
	protected function updatedExportCsv (BatchJob $dbBatchJob, vExportCsvJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return vFlowHelper::handleExportCsvFinished($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}

}

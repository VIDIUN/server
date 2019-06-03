<?php
class vDropFolderXmlEventsConsumer implements vBatchJobStatusEventConsumer, vObjectChangedEventConsumer
{
	const UPLOADED_BY = 'Drop Folder';
	const DROP_FOLDER_RESOURCE_NODE_NAME = 'dropFolderFileContentResource';
	const DROP_FOLDER_RESOURCE_PATH_ATTRIBUTE = 'filePath';
	const MYSQL_CODE_DUPLICATE_KEY = 23000;
	const MAX_XML_FILE_SIZE = 10485760;
	
	
	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::objectChanged()
	 */
	public function objectChanged(BaseObject $object, array $modifiedColumns) 
	{
		try 
		{
			$folder = DropFolderPeer::retrieveByPK($object->getDropFolderId());
			switch($object->getStatus())
			{
				case DropFolderFileStatus::PENDING:
					$this->onXmlDropFolderFileStatusChangedToPending($folder, $object);
					break;
				case DropFolderFileStatus::PURGED:
					$this->onXmlDropFolderFileStatusChangedToPurged($folder, $object);
					break;
			}
		}
		catch(Exception $e)
		{
			VidiunLog::err('Failed to process objectChangedEvent for drop folder file ['.$object->getDropFolderId().'] - '.$e->getMessage());
		}
		return true;
	}

	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::shouldConsumeChangedEvent()
	 */
	public function shouldConsumeChangedEvent(BaseObject $object, array $modifiedColumns) 
	{
		try 
		{
			if(	$object instanceof DropFolderFile && 
				($object->getStatus() == DropFolderFileStatus::PENDING || $object->getStatus() == DropFolderFileStatus::PURGED) && 
				in_array(DropFolderFilePeer::STATUS, $modifiedColumns))
			{
				$folder = DropFolderPeer::retrieveByPK($object->getDropFolderId());
				
				if(!$folder)
				{
					VidiunLog::err('Failed to process shouldConsumeChangedEvent - Failed to retrieve drop folder with ID ' . $object->getDropFolderId());
					return false;
				}
				
				if($folder->getFileHandlerType() == DropFolderXmlBulkUploadPlugin::getFileHandlerTypeCoreValue(DropFolderXmlFileHandlerType::XML))
					return true;
			}
		}
		catch(Exception $e)
		{
			VidiunLog::err('Failed to process shouldConsumeChangedEvent - '.$e->getMessage());
		} 		
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		try 
		{
			$jobObjectType = DropFolderXmlBulkUploadPlugin::getBatchJobObjectTypeCoreValue(DropFolderBatchJobObjectType::DROP_FOLDER_FILE);
			$jobStatuses = array(BatchJob::BATCHJOB_STATUS_FINISHED, BatchJob::BATCHJOB_STATUS_FINISHED_PARTIALLY, BatchJob::BATCHJOB_STATUS_FAILED, BatchJob::BATCHJOB_STATUS_FATAL, BatchJob::BATCHJOB_STATUS_QUEUED,);
			if($dbBatchJob->getJobType() == BatchJobType::BULKUPLOAD && 
						$dbBatchJob->getObjectType() == $jobObjectType &&
						in_array($dbBatchJob->getStatus(), $jobStatuses))
			{
				$data = $dbBatchJob->getData();
				if($data instanceof vBulkUploadJobData && $data->getBulkUploadObjectType() == BulkUploadObjectType::ENTRY)
					return true;
			}	
		}
		catch(Exception $e)
		{
			VidiunLog::err('Failed to process shouldConsumeJobStatusEvent - '.$e->getMessage());
		}
		return false;	
	}
	
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{
		try 
		{
			$this->onBulkUploadJobStatusUpdated($dbBatchJob);
		}
		catch(Exception $e)
		{
			VidiunLog::err('Failed to process updatedJob - '.$e->getMessage());
		}
		return true;
	}
				
	private function onBulkUploadJobStatusUpdated(BatchJob $dbBatchJob)
	{
		$xmlDropFolderFile = DropFolderFilePeer::retrieveByPK($dbBatchJob->getObjectId());
		if(!$xmlDropFolderFile)
			return;		
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_QUEUED:
				$jobData = $dbBatchJob->getData();
				if(!is_null($jobData->getFilePath()))
				{
					$syncKey = $dbBatchJob->getSyncKey(BatchJob::FILE_SYNC_BATCHJOB_SUB_TYPE_BULKUPLOAD);
					if(!vFileSyncUtils::fileSync_exists($syncKey))
					{
						try
						{
							vFileSyncUtils::moveFromFile($jobData->getFilePath(), $syncKey, true);
						}
						catch (Exception $e)
						{
							VidiunLog::err($e);
							throw new APIException(APIErrors::BULK_UPLOAD_CREATE_CSV_FILE_SYNC_ERROR);
						}
					}

					$filePath = vFileSyncUtils::getLocalFilePathForKey($syncKey);
					$jobData->setFilePath($filePath);
					
					//save new info on the batch job
					$dbBatchJob->setData($jobData);
					$dbBatchJob->save();
				}
				break;
			case BatchJob::BATCHJOB_STATUS_FINISHED:
			case BatchJob::BATCHJOB_STATUS_FINISHED_PARTIALLY:
				$xmlDropFolderFile->setStatus(DropFolderFileStatus::HANDLED);
				$xmlDropFolderFile->save();
				break;
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				$relatedFiles = DropFolderFilePeer::retrieveByLeadIdAndStatuses($xmlDropFolderFile->getId(), array(DropFolderFileStatus::PROCESSING));
				foreach ($relatedFiles as $relatedFile) 
				{
					$this->setFileError($relatedFile, DropFolderFileStatus::ERROR_HANDLING, 
										DropFolderXmlBulkUploadPlugin::getErrorCodeCoreValue(DropFolderXmlBulkUploadErrorCode::ERROR_IN_BULK_UPLOAD),
										DropFolderXmlBulkUploadPlugin::ERROR_IN_BULK_UPLOAD_MESSAGE);
				}			
				break;				
		}		
		
	}
			
	private function setFileProcessing(DropFolderFile $file, array $relatedFiles)
	{
		$file->setStatus(DropFolderFileStatus::PROCESSING);
		$affectedRows = $file->save();
		if($affectedRows > 0)
		{
			foreach ($relatedFiles as $relatedFile) 
			{
				if($relatedFile->getId() != $file->getId())
				{
					$relatedFile->setStatus(DropFolderFileStatus::PROCESSING);
					$relatedFile->save();
				}
			}
		}
		return $affectedRows;
	}
	
	private function setFileError(DropFolderFile $file, $status, $errorCode, $errorDescription)
	{
		VidiunLog::err('Error with file ['.$file->getId().'] -'.$errorDescription);
		
		$file->setStatus($status);
		$file->setErrorCode($errorCode);
		$file->setErrorDescription($errorDescription);
		$file->save();				
		
	}

	/**
	 * Mark any PARSED files as PURGED in case the purged file is an XML
	 * @param DropFolder $folder
	 * @param DropFolderFile $file
	 */
	private function onXmlDropFolderFileStatusChangedToPurged(DropFolder $folder, DropFolderFile $file)
	{
		
		$xmlFileHandler = vDropFolderXmlFileHandler::getHandlerInstance($folder->getType());
		$xmlFileHandler->handlePurgedDropFolderFile($folder, $file);
	}
	
	/**
	 * Validate if all the files ready:
	 * 1. Yes: add BulkUpload job
	 * 2. No: set status to Waiting
	 * @param DropFolder $folder
	 * @param DropFolderFile $file
	 */
	private function onXmlDropFolderFileStatusChangedToPending(DropFolder $folder, DropFolderFile $file)
	{
		$relatedFiles = array();
		try 
		{
			$xmlFileHandler = vDropFolderXmlFileHandler::getHandlerInstance($folder->getType());
			$xmlFileHandler->handlePendingDropFolderFile($folder, $file);
		}
		catch (Exception $e)
		{
			VidiunLog::err("Error in  onXmlDropFolderFileStatusChangedToPending -".$e->getMessage());
			if($e->getCode() == DropFolderXmlBulkUploadPlugin::getErrorCodeCoreValue(DropFolderXmlBulkUploadErrorCode::ERROR_ADDING_BULK_UPLOAD))
			{
				foreach ($relatedFiles as $relatedFile) 
				{
					$this->setFileError($relatedFile, DropFolderFileStatus::ERROR_HANDLING, $e->getCode(), $e->getMessage());											
				}				
			}
			else
				$this->setFileError($file, DropFolderFileStatus::ERROR_HANDLING, $e->getCode(), $e->getMessage());														
		}
	}
	

}

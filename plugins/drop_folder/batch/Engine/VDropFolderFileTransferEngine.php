<?php
/**
 * This engine handles the basiC use-cases of drop folders- local, and remote FTP, SFTP.
 */
class VDropFolderFileTransferEngine extends VDropFolderEngine
{
	const IGNORE_PATTERNS_DEFAULT_VALUE  = '*.cache,*.aspx';
	
	/**
	 * @var vFileTransferMgr
	 */	
	protected $fileTransferMgr;

	
	public function watchFolder (VidiunDropFolder $folder)
	{
		$this->dropFolder = $folder;
		$this->fileTransferMgr =  self::getFileTransferManager($this->dropFolder);
		VidiunLog::info('Watching folder ['.$this->dropFolder->id.']');
						    										
		$physicalFiles = $this->getDropFolderFilesFromPhysicalFolder();
		if(count($physicalFiles) > 0)
			$dropFolderFilesMap = $this->loadDropFolderFiles();
		else 
			$dropFolderFilesMap = array();

		$maxModificationTime = 0;
		foreach ($physicalFiles as &$physicalFile)
		{
			/* @var $physicalFile FileObject */	
			$physicalFileName = $physicalFile->filename;
			$utfFileName = vString::stripUtf8InvalidChars($physicalFileName);
			
			if($physicalFileName != $utfFileName)
			{
				VidiunLog::info("File name [$physicalFileName] is not utf-8 compatible, Skipping file...");
				continue;
			}
			
			if(!vXml::isXMLValidContent($utfFileName))
			{
				VidiunLog::info("File name [$physicalFileName] contains invalid XML characters, Skipping file...");
				continue;
			}
			
			if ($this->dropFolder->incremental && $physicalFile->modificationTime < $this->dropFolder->lastFileTimestamp)
			{
				VidiunLog::info("File modification time [" . $physicalFile->modificationTime ."] predates drop folder last timestamp [". $this->dropFolder->lastFileTimestamp ."]. Skipping.");
				if (isset ($dropFolderFilesMap[$physicalFileName]))
					unset($dropFolderFilesMap[$physicalFileName]);
				continue;
			}
			
			if($this->validatePhysicalFile($physicalFileName))
			{
				$maxModificationTime = ($physicalFile->modificationTime > $maxModificationTime) ? $physicalFile->modificationTime : $maxModificationTime;
				VidiunLog::info('Watch file ['.$physicalFileName.']');
				if(!array_key_exists($physicalFileName, $dropFolderFilesMap))
				{
					try 
					{
						$lastModificationTime = $physicalFile->modificationTime;
						$fileSize = $physicalFile->fileSize;
						
						$this->handleFileAdded($physicalFileName, $fileSize, $lastModificationTime);
					}
					catch (Exception $e)
					{
						VidiunLog::err("Error handling drop folder file [$physicalFileName] " . $e->getMessage());
					}											
				}
				else //drop folder file entry found
				{
					$dropFolderFile = $dropFolderFilesMap[$physicalFileName];
					//if file exist in the folder remove it from the map
					//all the files that are left in a map will be marked as PURGED					
					unset($dropFolderFilesMap[$physicalFileName]);
					$this->handleExistingDropFolderFile($dropFolderFile);
				}					
			}					
		}
		foreach ($dropFolderFilesMap as $dropFolderFile) 
		{
			$this->handleFilePurged($dropFolderFile->id);
		}
		
		if ($this->dropFolder->incremental && $maxModificationTime > $this->dropFolder->lastFileTimestamp)
		{
			$updateDropFolder = new VidiunDropFolder();
			$updateDropFolder->lastFileTimestamp = $maxModificationTime;
			$this->dropFolderPlugin->dropFolder->update($this->dropFolder->id, $updateDropFolder);
		}
	}
	
	protected function fileExists ()
	{
		return $this->fileTransferMgr->fileExists($this->dropFolder->path);
	}
	
	protected function handleExistingDropFolderFile (VidiunDropFolderFile $dropFolderFile)
	{
		try 
		{
			$fullPath = $this->dropFolder->path.'/'.$dropFolderFile->fileName;
			$lastModificationTime = $this->fileTransferMgr->modificationTime($fullPath);
			$fileSize = $this->fileTransferMgr->fileSize($fullPath);
		}
		catch (Exception $e)
		{
			$closedStatuses = array(
				VidiunDropFolderFileStatus::HANDLED, 
				VidiunDropFolderFileStatus::PURGED, 
				VidiunDropFolderFileStatus::DELETED
			);
			
			//In cases drop folder is not configured with auto delete we want to verify that the status file is not in one of the closed statuses so 
			//we won't update it to error status
			if(!in_array($dropFolderFile->status, $closedStatuses))
			{
				//Currently "modificationTime" does not throw Exception since from php documentation not all servers support the ftp_mdtm feature
				VidiunLog::err('Failed to get modification time or file size for file ['.$fullPath.']');
				$this->handleFileError($dropFolderFile->id, VidiunDropFolderFileStatus::ERROR_HANDLING, VidiunDropFolderFileErrorCode::ERROR_READING_FILE, 
															DropFolderPlugin::ERROR_READING_FILE_MESSAGE. '['.$fullPath.']', $e);
			}
			return false;		
		}				 
				
		if($dropFolderFile->status == VidiunDropFolderFileStatus::UPLOADING)
		{
			$this->handleUploadingDropFolderFile($dropFolderFile, $fileSize, $lastModificationTime);
		}
		else
		{
			VidiunLog::info('Last modification time ['.$lastModificationTime.'] known last modification time ['.$dropFolderFile->lastModificationTime.']');
			$isLastModificationTimeUpdated = $dropFolderFile->lastModificationTime && $dropFolderFile->lastModificationTime != '' && ($lastModificationTime > $dropFolderFile->lastModificationTime);
			
			if($isLastModificationTimeUpdated) //file is replaced, add new entry
		 	{
		 		$this->handleFileAdded($dropFolderFile->fileName, $fileSize, $lastModificationTime);
		 	}
		 	else
		 	{
		 		$deleteTime = $dropFolderFile->updatedAt + $this->dropFolder->autoFileDeleteDays*86400;
		 		if(($dropFolderFile->status == VidiunDropFolderFileStatus::HANDLED && $this->dropFolder->fileDeletePolicy != VidiunDropFolderFileDeletePolicy::MANUAL_DELETE && time() > $deleteTime) ||
		 			$dropFolderFile->status == VidiunDropFolderFileStatus::DELETED)
		 		{
		 			$this->purgeFile($dropFolderFile);
		 		}
		 	}
		}
	}
	
	protected function handleUploadingDropFolderFile (VidiunDropFolderFile $dropFolderFile, $currentFileSize, $lastModificationTime)
	{
		if (!$currentFileSize) 
		{
			$this->handleFileError($dropFolderFile->id, VidiunDropFolderFileStatus::ERROR_HANDLING, VidiunDropFolderFileErrorCode::ERROR_READING_FILE, 
															DropFolderPlugin::ERROR_READING_FILE_MESSAGE.'['.$this->dropFolder->path.'/'.$dropFolderFile->fileName);
		}		
		else if ($currentFileSize != $dropFolderFile->fileSize)
		{
			$this->handleFileUploading($dropFolderFile->id, $currentFileSize, $lastModificationTime);
		}
		else // file sizes are equal
		{
			$time = time();
			$fileSizeLastSetAt = $this->dropFolder->fileSizeCheckInterval + $dropFolderFile->fileSizeLastSetAt;
			
			VidiunLog::info("time [$time] fileSizeLastSetAt [$fileSizeLastSetAt]");
			
			// check if fileSizeCheckInterval time has passed since the last file size update	
			if ($time > $fileSizeLastSetAt)
			{
				$this->handleFileUploaded($dropFolderFile->id, $lastModificationTime);
			}
		}
	}
	
	protected function handleFileAdded ($fileName, $fileSize, $lastModificationTime)
	{
		try 
		{
			$newDropFolderFile = new VidiunDropFolderFile();
	    	$newDropFolderFile->dropFolderId = $this->dropFolder->id;
	    	$newDropFolderFile->fileName = $fileName;
	    	$newDropFolderFile->fileSize = $fileSize;
	    	$newDropFolderFile->lastModificationTime = $lastModificationTime; 
	    	$newDropFolderFile->uploadStartDetectedAt = time();
			$dropFolderFile = $this->dropFolderFileService->add($newDropFolderFile);
			return $dropFolderFile;
		}
		catch(Exception $e)
		{
			VidiunLog::err('Cannot add new drop folder file with name ['.$fileName.'] - '.$e->getMessage());
			return null;
		}
	}
	
	protected function validatePhysicalFile ($physicalFile)
	{
		VidiunLog::log('Validating physical file ['.$physicalFile.']');
		
		$ignorePatterns = $this->dropFolder->ignoreFileNamePatterns;	
		if($ignorePatterns)
			$ignorePatterns = self::IGNORE_PATTERNS_DEFAULT_VALUE.','.$ignorePatterns;
		else
			$ignorePatterns = self::IGNORE_PATTERNS_DEFAULT_VALUE;			
		$ignorePatterns = array_map('trim', explode(',', $ignorePatterns));
		
		$isValid = true;
		try 
		{
			$fullPath = $this->dropFolder->path.'/'.$physicalFile;
			if ($physicalFile === '.' || $physicalFile === '..')
			{
				VidiunLog::info("Skipping linux current and parent folder indicators");
				$isValid = false;
			}
			else if (empty($physicalFile)) 
			{
				VidiunLog::err("File name is not set");
				$isValid = false;
			}
			else if(!$fullPath || !$this->fileTransferMgr->fileExists($fullPath))
			{
				VidiunLog::err("Cannot access physical file in path [$fullPath]");
				$isValid = false;				
			}
			else
			{
				foreach ($ignorePatterns as $ignorePattern)
				{
					if (!is_null($ignorePattern) && ($ignorePattern != '') && fnmatch($ignorePattern, $physicalFile)) 
					{
						VidiunLog::err("Ignoring file [$physicalFile] matching ignore pattern [$ignorePattern]");
						$isValid = false;
					}
				}
			}
		}
		catch(Exception $e)
		{
			VidiunLog::err("Failure validating physical file [$physicalFile] - ". $e->getMessage());
			$isValid = false;
		}
		return $isValid;
	}
	
	/** 
     * Init a vFileTransferManager acccording to folder type and login to the server
     * @throws Exception
     * 
     * @return vFileTransferMgr
     */
	public static function getFileTransferManager(VidiunDropFolder $dropFolder)
	{
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
	    $fileTransferMgr = vFileTransferMgr::getInstance(self::getFileTransferMgrType($dropFolder->type), $engineOptions);
	    
	    $host =null; $username=null; $password=null; $port=null;
	    $privateKey = null; $publicKey = null;
	    
	    if($dropFolder instanceof VidiunRemoteDropFolder)
	    {
	   		$host = $dropFolder->host;
	    	$port = $dropFolder->port;
	    	$username = $dropFolder->username;
	    	$password = $dropFolder->password;
	    }  
	    if($dropFolder instanceof VidiunSshDropFolder)
	    {
	    	$privateKey = $dropFolder->privateKey;
	    	$publicKey = $dropFolder->publicKey;
	    	$passPhrase = $dropFolder->passPhrase;  	    	
	    }

        // login to server
        if ($privateKey || $publicKey) 
        {
	       	$privateKeyFile = $privateKey ? vFile::createTempFile($privateKey, 'privateKey') : null;
        	$publicKeyFile = $publicKey ? vFile::createTempFile($publicKey, 'publicKey'): null;
        	$fileTransferMgr->loginPubKey($host, $username, $publicKeyFile, $privateKeyFile, $passPhrase, $port);        	
        }
        else 
        {
        	$fileTransferMgr->login($host, $username, $password, $port);        	
        }
		
		return $fileTransferMgr;		
	}

		/**
	 * This mapping is required since the Enum values of the drop folder and file transfer manager are not the same
	 * @param int $dropFolderType
	 */
	public static function getFileTransferMgrType($dropFolderType)
	{
		switch ($dropFolderType)
		{
			case VidiunDropFolderType::LOCAL:
				return vFileTransferMgrType::LOCAL;
			case VidiunDropFolderType::FTP:
				return vFileTransferMgrType::FTP;
			case VidiunDropFolderType::SCP:
				return vFileTransferMgrType::SCP;
			case VidiunDropFolderType::SFTP:
				return vFileTransferMgrType::SFTP;
			case VidiunDropFolderType::S3:
				return vFileTransferMgrType::S3;
			default:
				return $dropFolderType;				
		}
		
	}
	
	
	/**
	 * Update uploading details
	 * @param int $dropFolderFileId
	 * @param int $fileSize
	 * @param int $lastModificationTime
	 * @param int $uploadStartDetectedAt
	 */
	protected function handleFileUploading($dropFolderFileId, $fileSize, $lastModificationTime, $uploadStartDetectedAt = null)
	{
		try 
		{
			$updateDropFolderFile = new VidiunDropFolderFile();
			$updateDropFolderFile->fileSize = $fileSize;
			$updateDropFolderFile->lastModificationTime = $lastModificationTime;
			if($uploadStartDetectedAt)
			{
				$updateDropFolderFile->uploadStartDetectedAt = $uploadStartDetectedAt;
			}
			return $this->dropFolderFileService->update($dropFolderFileId, $updateDropFolderFile);
		}
		catch (Exception $e) 
		{
			$this->handleFileError($dropFolderFileId, VidiunDropFolderFileStatus::ERROR_HANDLING, VidiunDropFolderFileErrorCode::ERROR_UPDATE_FILE, 
									DropFolderPlugin::ERROR_UPDATE_FILE_MESSAGE, $e);
			return null;
		}						
	}
	
	/**
	 * Update upload details and set file status to PENDING
	 * @param int $dropFolderFileId
	 * @param int $lastModificationTime
	 */
	protected function handleFileUploaded($dropFolderFileId, $lastModificationTime)
	{
		try 
		{
			$updateDropFolderFile = new VidiunDropFolderFile();
			$updateDropFolderFile->lastModificationTime = $lastModificationTime;
			$updateDropFolderFile->uploadEndDetectedAt = time();
			$this->dropFolderFileService->update($dropFolderFileId, $updateDropFolderFile);
			return $this->dropFolderFileService->updateStatus($dropFolderFileId, VidiunDropFolderFileStatus::PENDING);			
		}
		catch(VidiunException $e)
		{
			$this->handleFileError($dropFolderFileId, VidiunDropFolderFileStatus::ERROR_HANDLING, VidiunDropFolderFileErrorCode::ERROR_UPDATE_FILE, 
									DropFolderPlugin::ERROR_UPDATE_FILE_MESSAGE, $e);
			return null;
		}
	}
	
	protected function purgeFile(VidiunDropFolderFile $dropFolderFile)
	{
		$fullPath = $this->dropFolder->path.'/'.$dropFolderFile->fileName;
		// physicaly delete the file
		$delResult = null;
		try 
		{
		    $delResult = $this->fileTransferMgr->delFile($fullPath);
		}
		catch (Exception $e) 
		{
			VidiunLog::err("Error when deleting drop folder file - ".$e->getMessage());
		    $delResult = null;
		}
		if (!$delResult) 
			$this->handleFileError($dropFolderFile->id, VidiunDropFolderFileStatus::ERROR_DELETING, VidiunDropFolderFileErrorCode::ERROR_DELETING_FILE, 
														 DropFolderPlugin::ERROR_DELETING_FILE_MESSAGE. '['.$fullPath.']');
		else
		 	$this->handleFilePurged($dropFolderFile->id);
	}
	
	protected function getDropFolderFilesFromPhysicalFolder()
	{
		if($this->fileTransferMgr->fileExists($this->dropFolder->path))
		{
			$physicalFiles = $this->fileTransferMgr->listFileObjects($this->dropFolder->path);
			if ($physicalFiles) 
			{
				VidiunLog::log('Found ['.count($physicalFiles).'] in the folder');			
			}		
			else
			{
				VidiunLog::info('No physical files found for drop folder id ['.$this->dropFolder->id.'] with path ['.$this->dropFolder->path.']');
				$physicalFiles = array();
			}
		}
		else 
		{
			throw new vFileTransferMgrException('Drop folder path not valid ['.$this->dropFolder->path.']', vFileTransferMgrException::remotePathNotValid);
		}

		VidiunLog::info("physical files: ");
		foreach ($physicalFiles as &$currlFile)
		{
			VidiunLog::info(print_r($currlFile, true));
		}
		
		return $physicalFiles;
	}
	
	public function processFolder (VidiunBatchJob $job, VidiunDropFolderContentProcessorJobData $data)
	{
		VBatchBase::impersonate($job->partnerId);
		
		/* @var $data VidiunWebexDropFolderContentProcessorJobData */
		$dropFolder = $this->dropFolderPlugin->dropFolder->get ($data->dropFolderId);
		
		switch ($data->contentMatchPolicy)
		{
			case VidiunDropFolderContentFileHandlerMatchPolicy::ADD_AS_NEW:
				$this->addAsNewContent($job, $data, $dropFolder);
				break;
			
			case VidiunDropFolderContentFileHandlerMatchPolicy::MATCH_EXISTING_OR_KEEP_IN_FOLDER:
				$this->addAsExistingContent($job, $data, null, $dropFolder);
				break;
				
			case VidiunDropFolderContentFileHandlerMatchPolicy::MATCH_EXISTING_OR_ADD_AS_NEW:
				$matchedEntry = $this->isEntryMatch($data);
				if($matchedEntry)
					$this->addAsExistingContent($job, $data, $matchedEntry, $dropFolder);
				else
					 $this->addAsNewContent($job, $data, $dropFolder);	
				break;			
			default:
				throw new vApplicativeException(VidiunDropFolderErrorCode::CONTENT_MATCH_POLICY_UNDEFINED, 'No content match policy is defined for drop folder'); 
				break;
		}
		
		VBatchBase::unimpersonate();
	}
	
	private function addAsNewContent(VidiunBatchJob $job, VidiunDropFolderContentProcessorJobData $data, VidiunDropFolder $dropFolder)
	{ 		
		$resource = $this->getIngestionResource($job, $data);
		$newEntry = new VidiunBaseEntry();
		$newEntry->conversionProfileId = $data->conversionProfileId;
		$newEntry->name = $data->parsedSlug;
		$newEntry->referenceId = $data->parsedSlug;
		$newEntry->userId = $data->parsedUserId;
		VBatchBase::$vClient->startMultiRequest();
		$addedEntry = VBatchBase::$vClient->baseEntry->add($newEntry, null);
		VBatchBase::$vClient->baseEntry->addContent($addedEntry->id, $resource);
		$result = VBatchBase::$vClient->doMultiRequest();
		
		if ($result [1] && $result[1] instanceof VidiunBaseEntry)
		{
			$entry = $result [1];
			$this->createCategoryAssociations ($dropFolder, $entry->userId, $entry->id);
		}	
	}

	private function isEntryMatch(VidiunDropFolderContentProcessorJobData $data)
	{
		try 
		{
			$entryFilter = new VidiunBaseEntryFilter();
			$entryFilter->referenceIdEqual = $data->parsedSlug;
			$entryFilter->statusIn = VidiunEntryStatus::IMPORT.','.VidiunEntryStatus::PRECONVERT.','.VidiunEntryStatus::READY.','.VidiunEntryStatus::PENDING.','.VidiunEntryStatus::NO_CONTENT;		
			
			$entryPager = new VidiunFilterPager();
			$entryPager->pageSize = 1;
			$entryPager->pageIndex = 1;
			$entryList = VBatchBase::$vClient->baseEntry->listAction($entryFilter, $entryPager);
			
			if (is_array($entryList->objects) && isset($entryList->objects[0]) ) 
			{
				$result = $entryList->objects[0];
				if ($result->referenceId === $data->parsedSlug) 
					return $result;
			}
			
			return false;			
		}
		catch (Exception $e)
		{
			VidiunLog::err('Failed to get entry by reference id: [$data->parsedSlug] - '. $e->getMessage() );
			return false;
		}
	}
	
	/**
	 * Match the current file to an existing entry and flavor according to the slug regex.
	 * Update the matched entry with the new file and all other relevant files from the drop folder, according to the ingestion profile.
	 *
	 */
	private function addAsExistingContent(VidiunBatchJob $job, VidiunDropFolderContentProcessorJobData $data, $matchedEntry = null, VidiunDropFolder $dropFolder)
	{	    
		// check for matching entry and flavor
		if(!$matchedEntry)
		{
			$matchedEntry = $this->isEntryMatch($data);
			if(!$matchedEntry)
			{
				$e = new vTemporaryException('No matching entry found', VidiunDropFolderFileErrorCode::FILE_NO_MATCH);
				if(($job->createdAt + VBatchBase::$taskConfig->params->maxTimeBeforeFail) >= time())
				{
					$e->setResetJobExecutionAttempts(true);
				}	
				throw $e;		
			}
		}
		
		$resource = $this->getIngestionResource($job, $data);
		
		//If entry user ID differs from the parsed user ID on the job data - update the entry
		VBatchBase::$vClient->startMultiRequest();
		if ($data->parsedUserId != $matchedEntry->userId)
		{
			$updateEntry = new VidiunMediaEntry();
			$updateEntry->userId = $data->parsedUserId;
			VBatchBase::$vClient->baseEntry->update ($matchedEntry->id, $updateEntry);
		}
		VBatchBase::$vClient->media->cancelReplace($matchedEntry->id);
		$updatedEntry = VBatchBase::$vClient->baseEntry->updateContent($matchedEntry->id, $resource, $data->conversionProfileId);
		$result = VBatchBase::$vClient->doMultiRequest();
		
		if ($updatedEntry && $updatedEntry instanceof VidiunBaseEntry)
		{
			$this->createCategoryAssociations ($dropFolder, $updatedEntry->userId, $updatedEntry->id);
		}
	}

}

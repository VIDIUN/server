<?php
/**
 * 
 */
class VWebexDropFolderEngine extends VDropFolderEngine
{
	const ZERO_DATE = '12/31/1971 00:00:01';
	const ARF_FORMAT = 'ARF';
	const MAX_QUERY_DATE_RANGE_DAYS = 25; //Maximum querying date range is 28 days we define it as less than that
	const MIN_TIME_BEFORE_HANDLING_UPLOADING = 60; //the time in seconds
	const ADMIN_TAG_WEBEX = 'webexentry';
	private static $unsupported_file_formats = array('WARF');
	private $serviceTypes = null;
	private $dropFolderFilesMap = null;
	/**
	 * Webex wrapper
	 * @var webexWrapper
	 */
	private $webexWrapper;

	private function getServiceTypes()
	{
		if(!$this->serviceTypes)
		{
			$dropFolderServiceTypes = $this->dropFolder->webexServiceType ? explode(',', $this->dropFolder->webexServiceType) :
				array(WebexXmlComServiceTypeType::_MEETINGCENTER);
			$this->serviceTypes = webexWrapper::stringServicesTypesToWebexXmlArray($dropFolderServiceTypes);
		}

		return $this->serviceTypes;
	}

	/**
	 * @param $dropFolder VidiunWebexDropFolder
	 */
	public function setDropFolder($dropFolder)
	{
		$this->dropFolder = $dropFolder;
	}

	public function watchFolder(VidiunDropFolder $dropFolder)
	{
		/* @var $dropFolder VidiunWebexDropFolder */
		$this->dropFolder = $dropFolder;
		$this->webexWrapper = new webexWrapper($this->dropFolder->webexServiceUrl . '/' . $this->dropFolder->path, $this->getWebexClientSecurityContext(),
			array('VidiunLog', 'err'), array('VidiunLog', 'debug'));

		VidiunLog::info('Watching folder ['.$this->dropFolder->id.']');
		$startTime = null;
		$endTime = null;
		if ($this->dropFolder->incremental)
		{
			$startTime = time()-self::MAX_QUERY_DATE_RANGE_DAYS*86400;
			$pastPeriod = $this->getMaximumExecutionTime() ?  $this->getMaximumExecutionTime() : 3600;
			if ( $this->dropFolder->lastFileTimestamp && ( ($this->dropFolder->lastFileTimestamp - $pastPeriod) > (time()-self::MAX_QUERY_DATE_RANGE_DAYS*86400)) )
				$startTime = $this->dropFolder->lastFileTimestamp - $pastPeriod;
			
			$startTime = date('m/j/Y H:i:s', $startTime);
			$endTime = (date('m/j/Y H:i:s', time()+86400));
		}

		$result = $this->listAllRecordings($startTime, $endTime);
		if (!empty($result))
		{
			$this->HandleNewFiles($result);
		}
		else
		{
			VidiunLog::info('No new files to handle at this time');
		}

		if ($this->dropFolder->fileDeletePolicy != VidiunDropFolderFileDeletePolicy::MANUAL_DELETE)
		{
			$this->purgeFiles();
		}
	}

	private function getDropFolderFilesMap()
	{
		if(!$this->dropFolderFilesMap)
		{
			$this->dropFolderFilesMap = $this->loadDropFolderFiles();
		}

		return $this->dropFolderFilesMap;
	}

	/**
	 * @param $physicalFiles array
	 * @return vWebexHandleFilesResult
	 */
	public function HandleNewFiles($physicalFiles)
	{
		$result = new vWebexHandleFilesResult();
		$dropFolderFilesMap = $this->getDropFolderFilesMap();
		$maxTime = $this->dropFolder->lastFileTimestamp;
		foreach ($physicalFiles as $physicalFile)
		{
			/* @var $physicalFile WebexXmlEpRecordingType */
			$physicalFileName = $physicalFile->getName() . '_' . $physicalFile->getRecordingID();
			if (in_array($physicalFile->getFormat(),self::$unsupported_file_formats))
			{
				VidiunLog::info('Recording with id [' . $physicalFile->getRecordingID() . '] format [' . $physicalFile->getFormat() . '] is incompatible with the Vidiun conversion processes. Ignoring.');
				$result->addFileName(vWebexHandleFilesResult::FILE_NOT_ADDED_TO_DROP_FOLDER, $physicalFileName);
				continue;
			}

			if(!array_key_exists($physicalFileName, $dropFolderFilesMap))
			{
				if($this->handleFileAdded($physicalFile))
				{
					$maxTime = max(strtotime($physicalFile->getCreateTime()), $maxTime);
					VidiunLog::info("Added new file with name [$physicalFileName]. maxTime updated: $maxTime");
					$result->addFileName(vWebexHandleFilesResult::FILE_ADDED_TO_DROP_FOLDER, $physicalFileName);
				}
				else
					$result->addFileName(vWebexHandleFilesResult::FILE_NOT_ADDED_TO_DROP_FOLDER, $physicalFileName);
			}
			else //drop folder file entry found
			{
				$dropFolderFile = $dropFolderFilesMap[$physicalFileName];
				unset($dropFolderFilesMap[$physicalFileName]);
				if ($dropFolderFile->status == VidiunDropFolderFileStatus::UPLOADING && $this->handleExistingDropFolderFile($dropFolderFile))
					$result->addFileName(vWebexHandleFilesResult::FILE_HANDLED, $physicalFileName);
				else
					$result->addFileName(vWebexHandleFilesResult::FILE_NOT_HANDLED, $physicalFileName);
			}
		}

		if ($this->dropFolder->incremental && $maxTime > $this->dropFolder->lastFileTimestamp)
		{
			$updateDropFolder = new VidiunDropFolder();
			$updateDropFolder->lastFileTimestamp = $maxTime;
			$this->dropFolderPlugin->dropFolder->update($this->dropFolder->id, $updateDropFolder);
		}

		return $result;
	}

	public function handleUploadingFiles()
	{
		$minHandlingTime = time() - self::MIN_TIME_BEFORE_HANDLING_UPLOADING;
		$dropFolderFilesMap = $this->loadDropFolderUpLoadingFiles($minHandlingTime);
		foreach($dropFolderFilesMap as $name => $dropFolderFile)
		{
			$this->handleExistingDropFolderFile($dropFolderFile);
		}
	}

	public function processFolder (VidiunBatchJob $job, VidiunDropFolderContentProcessorJobData $data)
	{
		VBatchBase::impersonate ($job->partnerId);
		
		/* @var $data VidiunWebexDropFolderContentProcessorJobData */
		$dropFolder = $this->dropFolderPlugin->dropFolder->get ($data->dropFolderId);
		//In the case of the webex drop folder engine, the only possible contentMatch policy is ADD_AS_NEW.
		//Any other policy should cause an error.
		switch ($data->contentMatchPolicy)
		{
			case VidiunDropFolderContentFileHandlerMatchPolicy::ADD_AS_NEW:
				$this->addAsNewContent($job, $data, $dropFolder);
				break;
			default:
				throw new vApplicativeException(VidiunDropFolderErrorCode::DROP_FOLDER_APP_ERROR, 'Content match policy not allowed for Webex drop folders');
				break;
		}
		
		VBatchBase::unimpersonate();
	}

	protected function listRecordings ($startTime = null, $endTime = null, $startFrom = 1)
	{
		VidiunLog::info("Fetching list of recordings from Webex, startTime [$startTime], endTime [$endTime] of types ".print_r($this->getServiceTypes()));
		$result = $this->webexWrapper->listRecordings($this->getServiceTypes(), $startTime, $endTime, $startFrom);
		if($result)
		{
			$recording = $result->getRecording();
			VidiunLog::info('Recordings fetched: ' . print_r($recording, true));
		}

		return $result;
	}

	protected function listAllRecordings ($startTime = null, $endTime = null)
	{
		VidiunLog::info("Fetching list of all recordings from Webex, startTime [$startTime], endTime [$endTime] of types ".print_r($this->getServiceTypes()));
		$result = $this->webexWrapper->listAllRecordings($this->getServiceTypes(), $startTime, $endTime);
		VidiunLog::info('Recordings fetched: '.print_r($result, true) );
		return $result;
	}

	public function getWebexClientSecurityContext()
	{
		$securityContext = new WebexXmlSecurityContext();
		$securityContext->setUid($this->dropFolder->webexUserId); // webex username
		$securityContext->setPwd($this->dropFolder->webexPassword); // webex password
		$securityContext->setSiteName($this->dropFolder->webexSiteName); // webex partner id
		$securityContext->setSid($this->dropFolder->webexSiteId); // webex site id
		$securityContext->setPid($this->dropFolder->webexPartnerId); // webex partner id

		return $securityContext;
	}
	
	/**
	 * @throws Exception
	 */
	protected function purgeFiles ()
	{
		$createTimeEnd = date('m/j/Y H:i:s');
		$createTimeStart = date('m/j/Y H:i:s', time() - self::MAX_QUERY_DATE_RANGE_DAYS * 86400);
		if ($this->dropFolder->deleteFromTimestamp && $this->dropFolder->deleteFromTimestamp > (time() - self::MAX_QUERY_DATE_RANGE_DAYS * 86400))
			$createTimeStart = date('m/j/Y H:i:s', $this->dropFolder->deleteFromTimestamp);

		VidiunLog::info("Finding files to purge.");
		$result = $this->listAllRecordings($createTimeStart, $createTimeEnd);
		if($result)
		{
			VidiunLog::info("Files to delete: " . count($result));
			$dropFolderFilesMap = $this->getDropFolderFilesMap();
		}

		foreach ($result as $file)
		{
			$physicalFileName = $file->getName() . '_' . $file->getRecordingID();
			if (!$this->shouldPurgeFile($dropFolderFilesMap, $physicalFileName))
				continue;

			try
			{
				$this->webexWrapper->deleteRecordById($file->getRecordingID());
			}
			catch (Exception $e)
			{
				VidiunLog::err('Error occurred: ' . print_r($e, true));
				continue;
			}

			if ($this->dropFolder->deleteFromRecycleBin)
			{
				try
				{
					$this->webexWrapper->deleteRecordByName($file->getName(), $this->getServiceTypes(), true);
				}
				catch (Exception $e)
				{
					VidiunLog::err("File [$physicalFileName] could not be removed from recycle bin. Purge manually");
					continue;
				}
			}

			VidiunLog::info("File [$physicalFileName] successfully purged. Purging drop folder file");
			$this->dropFolderFileService->updateStatus($dropFolderFilesMap[$physicalFileName]->id, VidiunDropFolderFileStatus::PURGED);
		}
	}

	/**
	 * @param array $dropFolderFilesMap
	 * @param string $physicalFileName
	 * @return bool
	 */
	private function shouldPurgeFile($dropFolderFilesMap, $physicalFileName)
	{
		if (!array_key_exists($physicalFileName, $dropFolderFilesMap))
		{
			VidiunLog::info("File with name $physicalFileName not handled yet. Ignoring");
			return false;
		}

		$dropFolderFile = $dropFolderFilesMap[$physicalFileName];
		/* @var $dropFolderFile VidiunWebexDropFolderFile */
		if (!in_array($dropFolderFile->status, array(VidiunDropFolderFileStatus::HANDLED, VidiunDropFolderFileStatus::DELETED)))
		{
			VidiunLog::info("File with name $physicalFileName not in final status. Ignoring");
			return false;
		}

		$deleteTime = $dropFolderFile->updatedAt + $this->dropFolder->autoFileDeleteDays*86400;
		if (time() < $deleteTime)
		{
			VidiunLog::info("File with name $physicalFileName- not time to delete yet. Ignoring");
			return false;
		}

		VidiunLog::info("Going to purge file:$physicalFileName.");
		return true;
	}
	
	protected function handleFileAdded (WebexXmlEpRecordingType $webexFile)
	{
		try 
		{
			$newDropFolderFile = new VidiunWebexDropFolderFile();
	    	$newDropFolderFile->dropFolderId = $this->dropFolder->id;
	    	$newDropFolderFile->fileName = $webexFile->getName() . '_' . $webexFile->getRecordingID();
	    	$newDropFolderFile->fileSize = WebexPlugin::getSizeFromWebexContentUrl($webexFile->getFileURL());
	    	$newDropFolderFile->lastModificationTime = $webexFile->getCreateTime(); 
			$newDropFolderFile->description = $webexFile->getDescription();
			$newDropFolderFile->confId = $webexFile->getConfID();
			$newDropFolderFile->recordingId = $webexFile->getRecordingID();
			$newDropFolderFile->webexHostId = $webexFile->getHostWebExID();
			$newDropFolderFile->contentUrl = $webexFile->getFileURL();
			VidiunLog::debug("Adding new WebexDropFolderFile: " . print_r($newDropFolderFile, true));
			$dropFolderFile = $this->dropFolderFileService->add($newDropFolderFile);
			
			return $dropFolderFile;
		}
		catch(Exception $e)
		{
			VidiunLog::err('Cannot add new drop folder file with name ['.$webexFile->getName() . '_' . $webexFile->getRecordingID().'] - '.$e->getMessage());
			return null;
		}
	}

	protected function handleExistingDropFolderFile (VidiunWebexDropFolderFile $dropFolderFile)
	{
		try
		{
			$updatedFileSize = WebexPlugin::getSizeFromWebexContentUrl($dropFolderFile->contentUrl);
		}
		catch (Exception $e)
		{
			$this->handleFileError($dropFolderFile->id, VidiunDropFolderFileStatus::ERROR_HANDLING, VidiunDropFolderFileErrorCode::ERROR_READING_FILE,
					DropFolderPlugin::ERROR_READING_FILE_MESSAGE, $e);
			return null;
		}

		if (!$dropFolderFile->fileSize)
		{
			$this->handleFileError($dropFolderFile->id, VidiunDropFolderFileStatus::ERROR_HANDLING, VidiunDropFolderFileErrorCode::ERROR_READING_FILE,
				DropFolderPlugin::ERROR_READING_FILE_MESSAGE . '[' . $dropFolderFile->contentUrl . ']');
		}
		else if ($dropFolderFile->fileSize < $updatedFileSize)
		{
			try
			{
				$updateDropFolderFile = new VidiunDropFolderFile();
				$updateDropFolderFile->fileSize = $updatedFileSize;

				return $this->dropFolderFileService->update($dropFolderFile->id, $updateDropFolderFile);
			}
			catch (Exception $e)
			{
				$this->handleFileError($dropFolderFile->id, VidiunDropFolderFileStatus::ERROR_HANDLING, VidiunDropFolderFileErrorCode::ERROR_UPDATE_FILE,
					DropFolderPlugin::ERROR_UPDATE_FILE_MESSAGE, $e);
				return null;
			}
		}
		else // file sizes are equal
		{
			$time = time();
			$fileSizeLastSetAt = $this->dropFolder->fileSizeCheckInterval + $dropFolderFile->fileSizeLastSetAt;

			VidiunLog::info("time [$time] fileSizeLastSetAt [$fileSizeLastSetAt]");

			// check if fileSizeCheckInterval time has passed since the last file size update
			if ($time > $fileSizeLastSetAt) {
				try {
					return $this->dropFolderFileService->updateStatus($dropFolderFile->id, VidiunDropFolderFileStatus::PENDING);
				} catch (VidiunException $e) {
					$this->handleFileError($dropFolderFile->id, VidiunDropFolderFileStatus::ERROR_HANDLING, VidiunDropFolderFileErrorCode::ERROR_UPDATE_FILE,
						DropFolderPlugin::ERROR_UPDATE_FILE_MESSAGE, $e);
					return null;
				}
			}
		}
	}

	protected function addAsNewContent (VidiunBatchJob $job, VidiunWebexDropFolderContentProcessorJobData $data, VidiunWebexDropFolder $folder)
	{
		/* @var $data VidiunWebexDropFolderContentProcessorJobData */
		$resource = $this->getIngestionResource($job, $data);
		$newEntry = new VidiunMediaEntry();
		$newEntry->mediaType = VidiunMediaType::VIDEO;
		$newEntry->conversionProfileId = $data->conversionProfileId;
		$newEntry->name = $data->parsedSlug;
		$newEntry->description = $data->description;
		$newEntry->userId = $data->parsedUserId ? $data->parsedUserId : $this->retrieveUserFromWebexHostId($data, $folder);
		$newEntry->creatorId = $newEntry->userId;
		$newEntry->referenceId = $data->parsedSlug;
		$newEntry->adminTags = self::ADMIN_TAG_WEBEX;
			
		VBatchBase::$vClient->startMultiRequest();
		$addedEntry = VBatchBase::$vClient->media->add($newEntry, null);
		VBatchBase::$vClient->baseEntry->addContent($addedEntry->id, $resource);
		$result = VBatchBase::$vClient->doMultiRequest();
		
		if ($result [1] && $result[1] instanceof VidiunBaseEntry)
		{
			$entry = $result [1];
			$this->createCategoryAssociations ($folder, $entry->userId, $entry->id);
		}
	}

	protected function retrieveUserFromWebexHostId (VidiunWebexDropFolderContentProcessorJobData $data, VidiunWebexDropFolder $folder)
	{
		if ($folder->metadataProfileId && $folder->webexHostIdMetadataFieldName && $data->webexHostId)
		{
			$filter = new VidiunUserFilter();
			$filter->advancedSearch = new VidiunMetadataSearchItem();
			$filter->advancedSearch->metadataProfileId = $folder->metadataProfileId;
			$webexHostIdSearchCondition = new VidiunSearchCondition();
			$webexHostIdSearchCondition->field = $folder->webexHostIdMetadataFieldName;
			$webexHostIdSearchCondition->value = $data->webexHostId;
			$filter->advancedSearch->items = array($webexHostIdSearchCondition);
			try
			{
				$result = VBatchBase::$vClient->user->listAction ($filter, new VidiunFilterPager());
				
				if ($result->totalCount)
				{
					$user = $result->objects[0];
					return $user->id;
				}
			}
			catch (Exception $e)
			{
				VidiunLog::err('Error encountered. Code: ['. $e->getCode() . '] Message: [' . $e->getMessage() . ']');
			}

		}
		
		return $data->webexHostId;
	}
	
}

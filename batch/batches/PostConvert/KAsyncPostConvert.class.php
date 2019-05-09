<?php
/**
 * @package Scheduler
 * @subpackage Post-Convert
 */

/**
 * Will convert a single flavor and store it in the file system.
 * The state machine of the job is as follows:
 * 	 	get the flavor
 * 		convert using the right method
 * 		save recovery file in case of crash
 * 		move the file to the archive
 * 		set the entry's new status and file details
 *
 *
 * @package Scheduler
 * @subpackage Post-Convert
 */
class VAsyncPostConvert extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::POSTCONVERT;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->postConvert($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}

	/**
	 * @param VidiunBatchJob $job
	 * @param VidiunPostConvertJobData $data
	 * @return VidiunBatchJob
	 */
	private function postConvert(VidiunBatchJob $job, VidiunPostConvertJobData $data)
	{
		if($data->flavorParamsOutputId)
			$data->flavorParamsOutput = VBatchBase::$vClient->flavorParamsOutput->get($data->flavorParamsOutputId);
		
		try
		{
			$srcFileSyncDescriptor = reset($data->srcFileSyncs);
			$mediaFile = null;
			$key = null;
			if($srcFileSyncDescriptor) 
			{
				$mediaFile = trim($srcFileSyncDescriptor->fileSyncLocalPath);
				$key = $srcFileSyncDescriptor->fileEncryptionKey;
			}
				
			
			if(!$data->flavorParamsOutput || !$data->flavorParamsOutput->sourceRemoteStorageProfileId)
			{
				if(!$this->pollingFileExists($mediaFile))
					return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, "Source file $mediaFile does not exist", VidiunBatchJobStatus::RETRY);
				
				if(!is_file($mediaFile))
					return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, "Source file $mediaFile is not a file", VidiunBatchJobStatus::FAILED);
			}
			
			$this->updateJob($job,"Extracting file media info on $mediaFile", VidiunBatchJobStatus::QUEUED);
		}
		catch(Exception $ex)
		{
			return $this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), VidiunBatchJobStatus::FAILED);
		}
		
		$mediaInfo = null;
		try
		{
			$engine = VBaseMediaParser::getParser($job->jobSubType, realpath($mediaFile), VBatchBase::$taskConfig, $job);
			if($engine)
			{
				VidiunLog::info("Media info engine [" . get_class($engine) . "]");
				if (!$key)
					$mediaInfo = $engine->getMediaInfo();
				else 
				{
					$tempClearPath = self::createTempClearFile($mediaFile, $key);
					$engine->setFilePath($tempClearPath);
					$mediaInfo = $engine->getMediaInfo();
					unlink($tempClearPath);
				}
			}
			else
			{
				$err = "Media info engine not found for job subtype [".$job->jobSubType."]";
				VidiunLog::info($err);
				return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::ENGINE_NOT_FOUND, $err, VidiunBatchJobStatus::FAILED);
			}
		}
		catch(Exception $ex)
		{
			VidiunLog::err("Error: " . $ex->getMessage());
			$mediaInfo = null;
		}
		
		/* @var $mediaInfo VidiunMediaInfo */
		if(is_null($mediaInfo))
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::EXTRACT_MEDIA_FAILED, "Failed to extract media info: $mediaFile", VidiunBatchJobStatus::FAILED);

		/*
		 * Look for silent/black conversions. Curently checked only for Webex/ARF products
		 */
		$detectMsg = null;
		if(isset($data->flavorParamsOutput) && isset($data->flavorParamsOutput->operators)
		&& strstr($data->flavorParamsOutput->operators, "webexNbrplayer.WebexNbrplayer")!=false) {
			$rv = $this->checkForValidityOfWebexProduct($data, realpath($mediaFile), $mediaInfo, $detectMsg);
			if($rv==false){
				return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::BLACK_OR_SILENT_CONTENT, $detectMsg, VidiunBatchJobStatus::FAILED);
			}
		}


		try
		{
			$mediaInfo->flavorAssetId = $data->flavorAssetId;
			$createdMediaInfo = $this->getClient()->batch->addMediaInfo($mediaInfo);
			/* @var $createdMediaInfo VidiunMediaInfo */
			
			// must save the mediaInfoId before reporting that the task is finished
			$msg = "Saving media info id $createdMediaInfo->id";
			if(isset($detectMsg))
				$msg.= "($detectMsg)";
			$this->updateJob($job, $msg, VidiunBatchJobStatus::PROCESSED, $data);
			
			$data->thumbPath = null;
			if(!$data->createThumb)
				return $this->closeJob($job, null, null, "Media info id $createdMediaInfo->id saved", VidiunBatchJobStatus::FINISHED, $data);
			
			// creates a temp file path
			$rootPath = VBatchBase::$taskConfig->params->localTempPath;
			$this->createDir($rootPath);
				
			// creates the path
			$uniqid = uniqid('thumb_');
			$thumbPath = $rootPath . DIRECTORY_SEPARATOR . $uniqid;
			
			$videoDurationSec = floor($mediaInfo->videoDuration / 1000);
			$data->thumbOffset = max(0 ,min($data->thumbOffset, $videoDurationSec));
			
			if($mediaInfo->videoHeight)
				$data->thumbHeight = $mediaInfo->videoHeight;
			
			if($mediaInfo->videoBitRate)
				$data->thumbBitrate = $mediaInfo->videoBitRate;
					
			// generates the thumbnail
			$thumbMaker = new VFFMpegThumbnailMaker($mediaFile, $thumbPath, VBatchBase::$taskConfig->params->FFMpegCmd);
			$params['dar'] = $mediaInfo->videoDar;
			$params['scanType'] = $mediaInfo->scanType;
			if( $data->flavorAssetEncryptionKey )
			{
				$params['encryption_key'] = $data->flavorAssetEncryptionKey;
			}

			$created = $thumbMaker->createThumnail($data->thumbOffset, $mediaInfo->videoWidth, $mediaInfo->videoHeight, $params);
			
			if(!$created || !file_exists($thumbPath))
			{
				$data->createThumb = false;
				return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::THUMBNAIL_NOT_CREATED, 'Thumbnail not created', VidiunBatchJobStatus::FINISHED, $data);
			}
			$data->thumbPath = $thumbPath;
			
			$job = $this->moveFile($job, $data);
			
			if($this->checkFileExists($job->data->thumbPath))
				return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED, $data);
			
			$data->createThumb = false;
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, 'File not moved correctly', VidiunBatchJobStatus::FINISHED, $data);
		}
		catch(Exception $ex)
		{
			return $this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), VidiunBatchJobStatus::FAILED);
		}
	}
	
	/**
	 * @param VidiunBatchJob $job
	 * @param VidiunPostConvertJobData $data
	 * @return VidiunBatchJob
	 */
	private function moveFile(VidiunBatchJob $job, VidiunPostConvertJobData $data)
	{
		// creates a temp file path
		$rootPath = VBatchBase::$taskConfig->params->sharedTempPath;
		if(! is_dir($rootPath))
		{
			if(! file_exists($rootPath))
			{
				VidiunLog::info("Creating temp thumbnail directory [$rootPath]");
				mkdir($rootPath);
			}
			else
			{
				// already exists but not a directory
				$err = "Cannot create temp thumbnail directory [$rootPath] due to an error. Please fix and restart";
				throw new Exception($err, -1);
			}
		}
		
		$uniqid = uniqid('thumb_');
		$sharedFile = realpath($rootPath) . DIRECTORY_SEPARATOR . $uniqid;
		
		clearstatcache();
		$fileSize = vFile::fileSize($data->thumbPath);
		rename($data->thumbPath, $sharedFile);
		if(!file_exists($sharedFile) || vFile::fileSize($sharedFile) != $fileSize)
		{
			$err = 'moving file failed';
			throw new Exception($err, -1);
		}
		
		$this->setFilePermissions($sharedFile);
		$data->thumbPath = $sharedFile;
		$job->data = $data;
		return $job;
	}
	
	/**
	 * Check for invalidly generated content files -
	 * - Silent or black content for at least 50% of the total duration
	 * - The detection duration - at least 2 sec
	 * - Applicable only to Webex sources
	 * @param VidiunBatchJob $job
	 * @param VidiunPostConvertJobData $data
	 * $param $mediaFile
	 * #param VidiunMediaInfo $mediaInfo
	 * @return boolean
	 */
	private function checkForValidityOfWebexProduct(VidiunPostConvertJobData $data, $srcFileName, VidiunMediaInfo $mediaInfo, &$detectMsg)
	{
		$rv = true;
		$detectMsg = null;
		/*
		 * Get silent and black portions
		 *
		list($silenceDetect, $blackDetect) = VFFMpegMediaParser::checkForSilentAudioAndBlackVideo(VBatchBase::$taskConfig->params->FFMpegCmd, $srcFileName, $mediaInfo);
		
		$detectMsg = $silenceDetect;
		if(isset($blackDetect))
			$detectMsg = isset($detectMsg)?"$detectMsg,$blackDetect":$blackDetect;
		*/
		/*
		 * Silent/Black does not cause validation failure, just a job message 
		 */
		if(isset($detectMsg)){
//			return false;
		}
		
		/*
		 * Get number of Webex operators that represent the number of conversion retries.
		 * Return success after the last retry, independently of whether the result is garbled or not.
		 * The assumption is that 3 retries will bring the number of garbled audios to acceptable rate.
		 * Therefore if the audio is still garbled, it is probably due to false detection,
		 * therefore DO NOT fail the asset.
		 */
		$operators = json_decode($data->flavorParamsOutput->operators);
		if($data->currentOperationSet<count($operators)-1) {
			if(VFFMpegMediaParser::checkForGarbledAudio(VBatchBase::$taskConfig->params->FFMpegCmd, $srcFileName, $mediaInfo)==true) {
				$detectMsg.= " Garbled Audio!";
				$rv = false;
			}
		}
		
		return $rv;
	}
}

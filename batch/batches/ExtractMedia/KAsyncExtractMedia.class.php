<?php
/**
 * @package Scheduler
 * @subpackage Extract-Media
 */

/**
 * Will extract the media info of a single file 
 *
 * @package Scheduler
 * @subpackage Extract-Media
 */
class VAsyncExtractMedia extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::EXTRACT_MEDIA;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->extract($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}
	
	/**
	 * Will take a single VidiunBatchJob and extract the media info for the given file
	 */
	private function extract(VidiunBatchJob $job, VidiunExtractMediaJobData $data)
	{
		$srcFileSyncDescriptor = reset($data->srcFileSyncs);
		$mediaFile = null;
		if($srcFileSyncDescriptor)
			$mediaFile = trim($srcFileSyncDescriptor->fileSyncLocalPath);
		
		if(!$this->pollingFileExists($mediaFile))
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, "Source file $mediaFile does not exist", VidiunBatchJobStatus::RETRY);
		
		if(!is_file($mediaFile))
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, "Source file $mediaFile is not a file", VidiunBatchJobStatus::FAILED);
		
		$this->updateJob($job, "Extracting file media info on $mediaFile", VidiunBatchJobStatus::QUEUED);
		
		$mediaInfo = $this->extractMediaInfo($job, $mediaFile);
		
		if(is_null($mediaInfo))
		{
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::EXTRACT_MEDIA_FAILED, "Failed to extract media info: $mediaFile", VidiunBatchJobStatus::RETRY);
		}
		
		if($data->calculateComplexity)
			$this->calculateMediaFileComplexity($mediaInfo, $mediaFile);
		
		if($data->detectGOP>0) {
			$this->detectMediaFileGOP($mediaInfo, $mediaFile, $data->detectGOP);
		}

		$duration = $mediaInfo->containerDuration;
		if(!$duration)
			$duration = $mediaInfo->videoDuration;
		if(!$duration)
			$duration = $mediaInfo->audioDuration;
		
		if($data->extractId3Tags)
			$this->extractId3Tags($mediaFile, $data, $duration);
		
		VidiunLog::debug("flavorAssetId [$data->flavorAssetId]");
		$mediaInfo->flavorAssetId = $data->flavorAssetId;
		$mediaInfo = $this->getClient()->batch->addMediaInfo($mediaInfo);
		$data->mediaInfoId = $mediaInfo->id;
		
		$this->updateJob($job, "Saving media info id $mediaInfo->id", VidiunBatchJobStatus::PROCESSED, $data);
		$this->closeJob($job, null, null, null, VidiunBatchJobStatus::FINISHED);
		
		return $job;
	}
	
	/**
	 * extractMediaInfo extract the file info using mediainfo and parse the returned data
	 *  
	 * @param string $mediaFile file full path
	 * @return VidiunMediaInfo or null for failure
	 */
	private function extractMediaInfo($job, $mediaFile)
	{
		$mediaInfo = null;
		try
		{
			$mediaFile = realpath($mediaFile);
			
			$engine = VBaseMediaParser::getParser($job->jobSubType, $mediaFile, self::$taskConfig, $job);
			if($engine)
			{
				$mediaInfo = $engine->getMediaInfo();
			}
			else
			{
				$err = "No media info parser engine found for job sub type [$job->jobSubType]";
				return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::ENGINE_NOT_FOUND, $err, VidiunBatchJobStatus::FAILED);
			}
		}
		catch(Exception $ex)
		{
			VidiunLog::err($ex->getMessage());
			$mediaInfo = null;
		}
		
		return $mediaInfo;
	}
	
	/*
	 * Calculate media file 'complexity'
	 */
	private function calculateMediaFileComplexity(&$mediaInfo, $mediaFile)
	{
		$complexityValue = null;
		
		if(isset(self::$taskConfig->params->localTempPath) && file_exists(self::$taskConfig->params->localTempPath))
		{
			$ffmpegBin = isset(self::$taskConfig->params->ffmpegCmd)? self::$taskConfig->params->ffmpegCmd: null;
			$ffprobeBin = isset(self::$taskConfig->params->ffprobeCmd)? self::$taskConfig->params->ffprobeCmd: null;
			$mediaInfoBin = isset(self::$taskConfig->params->mediaInfoCmd)? self::$taskConfig->params->mediaInfoCmd: null;
			$calcComplexity = new VMediaFileComplexity($ffmpegBin, $ffprobeBin, $mediaInfoBin);
			
			$baseOutputName = tempnam(self::$taskConfig->params->localTempPath, "/complexitySampled_".pathinfo($mediaFile, PATHINFO_FILENAME)).".mp4";
			$stat = $calcComplexity->EvaluateSampled($mediaFile, $mediaInfo, $baseOutputName);
			if(isset($stat->complexityValue))
			{
				VidiunLog::log("Complexity: value($stat->complexityValue)");
				if(isset($stat->y))
					VidiunLog::log("Complexity: y($stat->y)");
				
				$complexityValue = $stat->complexityValue;
			}
		}
		
		if($complexityValue)
			$mediaInfo->complexityValue = $complexityValue;
	}
	
	private function extractId3Tags($filePath, VidiunExtractMediaJobData $data, $duration)
	{
		try
		{
			$vidiunId3TagParser = new VSyncPointsMediaInfoParser($filePath);
			$syncPointArray = $vidiunId3TagParser->getStreamSyncPointData();
			
			$outputFileName = pathinfo($filePath, PATHINFO_FILENAME) . ".data";
			$localTempSyncPointsFilePath = self::$taskConfig->params->localTempPath . DIRECTORY_SEPARATOR . $outputFileName;
			$sharedTempSyncPointFilePath = self::$taskConfig->params->sharedTempPath . DIRECTORY_SEPARATOR . $outputFileName;

			$retries = 3;
			$interval = (self::$taskConfig->fileSystemCommandInterval ? self::$taskConfig->fileSystemCommandInterval : self::DEFAULT_SLEEP_INTERVAL);
			while ($retries-- > 0)
			{
				if (vFile::setFileContent($localTempSyncPointsFilePath, serialize($syncPointArray)) &&
					$this->moveDataFile($data, $localTempSyncPointsFilePath, $sharedTempSyncPointFilePath))
						return true;
				VidiunLog::log("Failed on moving syncPointArray to server, waiting $interval seconds");
				sleep($interval);
			}
			throw new vTemporaryException("Failed on moving syncPointArray to server. temp path: {$localTempSyncPointsFilePath}");
		}
		catch(vTemporaryException $vtex)
		{
			$this->unimpersonate();
			throw $vtex;
		}
		catch(Exception $ex) 
		{
			$this->unimpersonate();
			VidiunLog::warning("Failed to extract id3tags data or duration data with error: " . print_r($ex, true));
		}
		
	}
	
	private function moveDataFile(VidiunExtractMediaJobData $data, $localTempSyncPointsFilePath, $sharedTempSyncPointFilePath)
	{
		VidiunLog::debug("moving file from [$localTempSyncPointsFilePath] to [$sharedTempSyncPointFilePath]");
		$fileSize = vFile::fileSize($localTempSyncPointsFilePath);
		
		$res = vFile::moveFile($localTempSyncPointsFilePath, $sharedTempSyncPointFilePath, true);
		if (!$res)
			return false;
		clearstatcache();
		
		$this->setFilePermissions($sharedTempSyncPointFilePath);
		if(!$this->checkFileExists($sharedTempSyncPointFilePath, $fileSize))
		{
			VidiunLog::warning("Failed to move file to [$sharedTempSyncPointFilePath]");
			return false;
		}
		else
			$data->destDataFilePath = $sharedTempSyncPointFilePath;
		return true;
	}

	/*
	 *
	 */
	 private function detectMediaFileGOP($mediaInfo, $mediaFile, $interval)
	 {
		VidiunLog::log("Detection interval($interval)");
		list($minGOP,$maxGOP,$detectedGOP) = VFFMpegMediaParser::detectGOP((isset(self::$taskConfig->params->ffprobeCmd)? self::$taskConfig->params->ffprobeCmd: null), $mediaFile, 0, $interval);
		VidiunLog::log("Detected - minGOP($minGOP),maxGOP($maxGOP),detectedGOP($detectedGOP)");
		if(isset($maxGOP)){
			$mediaInfo->maxGOP = $maxGOP;
		}
	 }
}


<?php
/**
 * This worker converts recorded live media files to MPEG-TS
 *
 * @package Scheduler
 * @subpackage Conversion
 */
class VAsyncConvertLiveSegment extends VJobHandlerWorker
{
	/**
	 * constants duplicated from assetParams.php
	 */
	const TAG_RECORDING_ANCHOR = 'recording_anchor';

	/**
	 * @var string
	 */
	protected $localTempPath;
	
	/**
	 * @var string
	 */
	protected $sharedTempPath;
	
	/**
	 * (non-PHPdoc)
	 * @see VBatchBase::getJobType()
	 */
	protected function getJobType()
	{
		return VidiunBatchJobType::CONVERT_LIVE_SEGMENT;
	}
	
	public static function getType()
	{
		return VidiunBatchJobType::CONVERT_LIVE_SEGMENT;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::run()
	 */
	public function run($jobs = null)
	{
		// creates a temp file path
		$this->localTempPath = self::$taskConfig->params->localTempPath;
		$this->sharedTempPath = self::$taskConfig->params->sharedTempPath;
		
		$res = self::createDir($this->localTempPath);
		if(! $res)
		{
			VidiunLog::err("Cannot continue conversion without temp local directory");
			return null;
		}
		$res = self::createDir($this->sharedTempPath);
		if(! $res)
		{
			VidiunLog::err("Cannot continue conversion without temp shared directory");
			return null;
		}
		
		return parent::run($jobs);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->convert($job, $job->data);
	}
	
	protected function convert(VidiunBatchJob $job, VidiunConvertLiveSegmentJobData $data)
	{
		$this->updateJob($job, "File conversion started", VidiunBatchJobStatus::PROCESSING);
		$jobData = $job->data;

		$ffmpegBin = VBatchBase::$taskConfig->params->ffmpegCmd;
		$ffprobeBin = isset(VBatchBase::$taskConfig->params->ffprobeCmd)? VBatchBase::$taskConfig->params->ffprobeCmd: "ffprobe";

		$fileName = "{$job->entryId}_{$jobData->assetId}_{$data->mediaServerIndex}.{$job->id}.ts";
		$localTempFilePath = $this->localTempPath . DIRECTORY_SEPARATOR . $fileName;
		$sharedTempFilePath = $this->sharedTempPath . DIRECTORY_SEPARATOR . $fileName;

		$result = $this->convertRecordedToMPEGTS($ffmpegBin, $ffprobeBin, $data->srcFilePath, $localTempFilePath);
		if(! $result)
			return $this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, null, "Failed to convert file", VidiunBatchJobStatus::FAILED);

		// write AMF data to a file in shared storage
		self::generateAmfData($job, $data, $localTempFilePath);

		return $this->moveFile($job, $data, $localTempFilePath, $sharedTempFilePath);
	}

	protected function generateAmfData(VidiunBatchJob $job, VidiunConvertLiveSegmentJobData $data, $localTempFilePath)
	{
		$mediaInfoBin = isset(VBatchBase::$taskConfig->params->mediaInfoCmd)? VBatchBase::$taskConfig->params->mediaInfoCmd: "mediainfo";

		// only extract the data if it's the primary server since we don't use this data in the secondary
		if ($data->mediaServerIndex == VidiunEntryServerNodeType::LIVE_PRIMARY) {
			try {

				// get the asset to check if it has a assetParams::TAG_RECORDING_ANCHOR tag.
				// note that assetParams::TAG_RECORDING_ANCHOR is not exposed in the API so I use it's string value.
				VBatchBase::impersonate($job->partnerId);
				$asset = VBatchBase::$vClient->flavorAsset->get($data->assetId);
				VBatchBase::unimpersonate();
				if (strpos($asset->tags,self::TAG_RECORDING_ANCHOR) == false) {
					return;
				}

				// Extract AMF data from all data frames in the segment
				$amfParser = new VAMFMediaInfoParser($data->srcFilePath);
				$amfArray = $amfParser->getAMFInfo();

				// run mediaInfo on $localTempFilePath to get it's duration, and store it in the job data
				$mediaInfoParser = new VMediaInfoMediaParser($localTempFilePath, $mediaInfoBin);
				$duration = $mediaInfoParser->getMediaInfo()->videoDuration;

				array_unshift($amfArray, $duration);

				$amfFileName = "{$data->entryId}_{$data->assetId}_{$data->mediaServerIndex}_{$data->fileIndex}.data";
				$localTempAmfFilePath = $this->localTempPath . DIRECTORY_SEPARATOR . $amfFileName;
				$sharedTempAmfFilePath = $this->sharedTempPath . DIRECTORY_SEPARATOR . $amfFileName;

				file_put_contents($localTempAmfFilePath, serialize($amfArray));

				self::moveDataFile($data, $localTempAmfFilePath, $sharedTempAmfFilePath);
			}
			catch(Exception $ex) {
				VBatchBase::unimpersonate();
				VidiunLog::warning('failed to extract AMF data or duration data ' . print_r($ex));
			}
		}
	}

	/**
	 * @param VidiunBatchJob $job
	 * @param VidiunConcatJobData $data
	 * @param string $localTempFilePath
	 * @param string $sharedTempFilePath
	 * @return VidiunBatchJob
	 */
	protected function moveFile(VidiunBatchJob $job, VidiunConvertLiveSegmentJobData $data, $localTempFilePath, $sharedTempFilePath)
	{
		$this->updateJob($job, "Moving file from [$localTempFilePath] to [$sharedTempFilePath]", VidiunBatchJobStatus::MOVEFILE);
		
		vFile::moveFile($localTempFilePath, $sharedTempFilePath, true);
		clearstatcache();
		$fileSize = vFile::fileSize($sharedTempFilePath);
		
		$this->setFilePermissions($sharedTempFilePath);
		
		if(! $this->checkFileExists($sharedTempFilePath, $fileSize))
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, 'File not moved correctly', VidiunBatchJobStatus::RETRY);
		
		$data->destFilePath = $sharedTempFilePath;
		return $this->closeJob($job, null, null, 'Succesfully moved file', VidiunBatchJobStatus::FINISHED, $data);
	}

	protected function moveDataFile(VidiunConvertLiveSegmentJobData $data, $localTempAmfFilePath, $sharedTempAmfFilePath)
	{
		VidiunLog::debug('moving file from ' . $localTempAmfFilePath . ' to ' . $sharedTempAmfFilePath);
		vFile::moveFile($localTempAmfFilePath, $sharedTempAmfFilePath, true);
		clearstatcache();
		$fileSize = vFile::fileSize($sharedTempAmfFilePath);
		$this->setFilePermissions($sharedTempAmfFilePath);
		if(! $this->checkFileExists($sharedTempAmfFilePath, $fileSize))
			VidiunLog::warning('failed to move file to ' . $sharedTempAmfFilePath);
		else
			$data->destDataFilePath = $sharedTempAmfFilePath;
	}
	
	protected function convertRecordedToMPEGTS($ffmpegBin, $ffprobeBin, $inFilename, $outFilename)
	{
		$cmdStr = "$ffmpegBin -i $inFilename -c copy -bsf:v h264_mp4toannexb -f mpegts -y $outFilename 2>&1";
		
		VidiunLog::debug("Executing [$cmdStr]");
		$output = system($cmdStr, $rv);

		/*
		 * Anomaly detection -
		*	Look for the time of the first KF in the source file.
		*	Should be less than 200 msec
		*	Currnetly - just logging
		*/
		$detectInterval = 10;		// sec
		$maxKeyFrameTime = 0.200;	// sec
		$kfArr=VFFMpegMediaParser::retrieveKeyFrames($ffprobeBin, $inFilename,0,$detectInterval);
		VidiunLog::log("KeyFrames:".print_r($kfArr,1));
		if(count($kfArr)==0){
			VidiunLog::log("Anomaly detection: NO Keyframes in the detection interval ($detectInterval sec)");
		}
		else if($kfArr[0]>$maxKeyFrameTime){
			VidiunLog::log("Anomaly detection: ERROR, first KF at ($kfArr[0] sec), max allowed ($maxKeyFrameTime sec)");
		}
		else {
			VidiunLog::log("Anomaly detection: OK, first KF at ($kfArr[0] sec), max allowed ($maxKeyFrameTime sec)");
		}
		
		return ($rv == 0) ? true : false;
	}
}
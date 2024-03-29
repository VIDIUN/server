<?php
/**
 * @package Scheduler
 * @subpackage Conversion
 */

/**
 * Will close almost done conversions that sent to remote systems and store the files in the file system.
 * The state machine of the job is as follows:
 * 	 	get almost done conversions 
 * 		check the convert status
 * 		download the converted file
 * 		save recovery file in case of crash
 * 		move the file to the archive
 *
 * @package Scheduler
 * @subpackage Conversion
 */
class VAsyncConvertCloser extends VJobCloserWorker
{
	private $localTempPath;
	private $sharedTempPath;

	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::CONVERT;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->closeConvert($job, $job->data);
	}
	
	public function __construct($taskConfig = null)
	{
		parent::__construct($taskConfig);
		
		// creates a temp file path
		$this->localTempPath = self::$taskConfig->params->localTempPath;
		$this->sharedTempPath = self::$taskConfig->params->sharedTempPath;
		
	}
	
	public function run($jobs = null)
	{
		$res = self::createDir( $this->localTempPath );
		if ( !$res ) 
		{
			VidiunLog::err( "Cannot continue conversion without temp local directory");
			return null;
		}
		
		$res = self::createDir( $this->sharedTempPath );
		if ( !$res ) 
		{
			VidiunLog::err( "Cannot continue conversion without temp shared directory");
			return null;
		}
		
		return parent::run($jobs);
	}
	
	private function closeConvert(VidiunBatchJob $job, VidiunConvertJobData $data)
	{
		if(($job->queueTime + self::$taskConfig->params->maxTimeBeforeFail) < time())
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::CLOSER_TIMEOUT, 'Timed out', VidiunBatchJobStatus::FAILED);
		
		if(isset($data->flavorParamsOutputId))
			$data->flavorParamsOutput = self::$vClient->flavorParamsOutput->get($data->flavorParamsOutputId);
			
		$this->operationEngine = VOperationManager::getEngine($job->jobSubType, $data, $job);
		try 
		{
			$isDone = $this->operationEngine->closeOperation();
			if(!$isDone)
			{
				$message = "Conversion close in process. ";
				if($this->operationEngine->getMessage())
					$message = $message.$this->operationEngine->getMessage();
				return $this->closeJob($job, null, null, $message, VidiunBatchJobStatus::ALMOST_DONE, $data);
			}
		}
		catch(VOperationEngineException $e)
		{
			$err = "engine [" . get_class($this->operationEngine) . "] convert closer failed: " . $e->getMessage();
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::CONVERSION_FAILED, $err, VidiunBatchJobStatus::FAILED);			
		}
			
		if(self::$taskConfig->params->isRemoteOutput)
		{
			return $this->handleRemoteOutput($job, $data);
		}
		else
			return $this->closeJob($job, null, null, "Conversion finished", VidiunBatchJobStatus::FINISHED, $data);
	}
	
	private function handleRemoteOutput(VidiunBatchJob $job, VidiunConvertJobData $data)
	{
		if($job->executionAttempts > 1) // is a retry
		{
			if(	strlen($data->destFileSyncLocalPath) && file_exists($data->destFileSyncLocalPath) 
				&& $this->checkExtraDestFileSyncsFetched($data->extraDestFileSyncs))
			{
				return $this->moveFile($job, $data);
			}
		}
		// creates a temp file path
		$uniqid = uniqid('convert_');
		if($data->destFileSyncLocalPath)
		{
			$data->destFileSyncLocalPath = $this->localTempPath . DIRECTORY_SEPARATOR . $uniqid;
			$err = null;
			if(!$this->fetchFile($data->destFileSyncRemoteUrl, $data->destFileSyncLocalPath, $err))
			{
				return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::REMOTE_DOWNLOAD_FAILED, $err, VidiunBatchJobStatus::ALMOST_DONE);
			}
		}
		if(count($data->extraDestFileSyncs))
		{
			foreach ($data->extraDestFileSyncs as $destFileSync) 
			{
				$ext = pathinfo($destFileSync->fileSyncLocalPath, PATHINFO_EXTENSION);
				$destFileSync->fileSyncLocalPath = $this->localTempPath . DIRECTORY_SEPARATOR . $uniqid.'.'.$ext;
				$err = null;
				if(!$this->fetchFile($destFileSync->fileSyncRemoteUrl, $destFileSync->fileSyncLocalPath, $err))
				{
					return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::REMOTE_DOWNLOAD_FAILED, $err, VidiunBatchJobStatus::ALMOST_DONE);
				}
			}
		}
		$this->fetchFile($data->logFileSyncRemoteUrl, $data->logFileSyncLocalPath);
		
		return $this->moveFile($job, $data);
		
	}
	
	private function checkExtraDestFileSyncsFetched($extraDestFileSyncs = null)
	{
		if(!$extraDestFileSyncs || !count($extraDestFileSyncs))
			return true;
		foreach ($extraDestFileSyncs as $fileSync) 
		{
			if(!$fileSync->fileSyncLocalPath || !file_exists($fileSync->fileSyncLocalPath))
				return false;
		}
		return true;
	}
	
	private function moveFile(VidiunBatchJob $job, VidiunConvertJobData $data)
	{
		$uniqid = uniqid('convert_');
		$sharedFile = $this->sharedTempPath . DIRECTORY_SEPARATOR . $uniqid;
		
		try
		{
			rename($data->logFileSyncLocalPath, "$sharedFile.log");
		}
		catch(Exception $ex)
		{
			VidiunLog::err($ex);
		}
		
		clearstatcache();
		$fileSize = vFile::fileSize($data->destFileSyncLocalPath);
		$this->moveSingleFile($data->destFileSyncLocalPath, $sharedFile);
		
		$data->destFileSyncLocalPath = $sharedFile;
		$data->logFileSyncLocalPath = "$sharedFile.log";
		
		if(count($data->extraDestFileSyncs))
		{
			foreach ($data->extraDestFileSyncs as $destFileSync) 
			{
				$newFileName = $this->moveSingleFile($destFileSync->fileSyncLocalPath, $sharedFile, true);
				$destFileSync->fileSyncLocalPath = $newFileName;
			}
		}
		
		if($this->checkFileExists($sharedFile, $fileSize))
		{
			$job->status = VidiunBatchJobStatus::FINISHED;
			$job->message = "File moved to shared";
		}
		else
		{
			$job->status = VidiunBatchJobStatus::ALMOST_DONE; // retry
			$job->message = "File not moved correctly";
		}
		$updateData = new VidiunConvertJobData();
		$updateData->destFileSyncLocalPath = $data->destFileSyncLocalPath;
		$updateData->logFileSyncLocalPath = $data->logFileSyncLocalPath;
		$updateData->extraDestFileSyncs = $data->extraDestFileSyncs;
		
		return $this->closeJob($job, null, null, $job->message, $job->status, $updateData);
	}
	
	private function moveSingleFile($oldName, $newName, $setExt = false)
	{
		if($setExt)
		{
			$ext = pathinfo($oldName, PATHINFO_EXTENSION);
			$newName = $newName.'.'.$ext;
		}
		$fileSize = vFile::fileSize($oldName);
		rename($oldName, $newName);
		if(!file_exists($newName) || vFile::fileSize($newName) != $fileSize)
		{
			VidiunLog::err("Error: moving file failed: ".$oldName);
			die();
		}
		return $newName;
	}
	
	/**
	 * @param string $srcFileSyncRemoteUrl
	 * @param string $srcFileSyncLocalPath
	 * @param string $errDescription
	 * @return string
	 */
	private function fetchFile($srcFileSyncRemoteUrl, $srcFileSyncLocalPath, &$errDescription = null)
	{
		try
		{
			$curlWrapper = new VCurlWrapper();
			$curlHeaderResponse = $curlWrapper->getHeader($srcFileSyncRemoteUrl, true);
			if(!$curlHeaderResponse || $curlWrapper->getError())
			{
				$errDescription = "Error: " . $curlWrapper->getError();
				return false;
			}
			
			if($curlHeaderResponse->code != VCurlHeaderResponse::HTTP_STATUS_OK)
			{
				$errDescription = "HTTP Error: " . $curlHeaderResponse->code . " " . $curlHeaderResponse->codeName;
				return false;
			}
			$fileSize = null;
			if(isset($curlHeaderResponse->headers['content-length']))
				$fileSize = $curlHeaderResponse->headers['content-length'];
			$curlWrapper->close();
				
			$curlWrapper = new VCurlWrapper();
			$res = $curlWrapper->exec($srcFileSyncRemoteUrl, $srcFileSyncLocalPath, null, true);
			VidiunLog::debug("Curl results: $res");
		
			if(!$res || $curlWrapper->getError())
			{
				$errDescription = "Error: " . $curlWrapper->getError();
				$curlWrapper->close();
				return false;
			}
			$curlWrapper->close();
			
			if(!file_exists($srcFileSyncLocalPath))
			{
				$errDescription = "Error: output file doesn't exist";
				return false;
			}
				
			if($fileSize)
			{
				clearstatcache();
				if(vFile::fileSize($srcFileSyncLocalPath) != $fileSize)
				{
					$errDescription = "Error: output file have a wrong size";
					return false;
				}
			}
		}
		catch(Exception $ex)
		{
			$errDescription = "Error: " . $ex->getMessage();
			return false;
		}
		
		return true;
	}
}

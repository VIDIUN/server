<?php
/**
 * Will scan for viruses on specified file  
 *
 * @package plugins.virusScan
 * @subpackage Scheduler
 */
class VAsyncVirusScan extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::VIRUS_SCAN;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->scan($job, $job->data);
	}
	
	protected function scan(VidiunBatchJob $job, VidiunVirusScanJobData $data)
	{
		try
		{
			$engine = VirusScanEngine::getEngine($job->jobSubType);
			if (!$engine)
			{
				VidiunLog::err('Cannot create VirusScanEngine of type ['.$job->jobSubType.']');
				$this->closeJob($job, VidiunBatchJobErrorTypes::APP, null, 'Error: Cannot create VirusScanEngine of type ['.$job->jobSubType.']', VidiunBatchJobStatus::FAILED);
				return $job;
			}
						
			// configure engine
			if (!$engine->config(self::$taskConfig->params))
			{
				VidiunLog::err('Cannot configure VirusScanEngine of type ['.$job->jobSubType.']');
				$this->closeJob($job, VidiunBatchJobErrorTypes::APP, null, 'Error: Cannot configure VirusScanEngine of type ['.$job->jobSubType.']', VidiunBatchJobStatus::FAILED);
				return $job;
			}
			
			$cleanIfInfected = $data->virusFoundAction == VidiunVirusFoundAction::CLEAN_NONE || $data->virusFoundAction == VidiunVirusFoundAction::CLEAN_DELETE;
			$errorDescription = null;
			$output = null;
			
			// execute scan
			$key = $data->fileContainer->encryptionKey;
			if (!$key)
				$data->scanResult = $engine->execute($data->fileContainer->filePath, $cleanIfInfected, $output, $errorDescription);
			else
			{
				$tempPath = self::createTempClearFile($data->fileContainer->filePath, $key);
				$data->scanResult = $engine->execute($tempPath, $cleanIfInfected, $output, $errorDescription);
				unlink($tempPath);
			}

			if (!$output) {
				VidiunLog::notice('Virus scan engine ['.get_class($engine).'] did not return any log for file ['.$data->srcFilePath.']');
				$output = 'Virus scan engine ['.get_class($engine).'] did not return any log';
			}
		
			try
			{
				self::$vClient->batch->logConversion($data->flavorAssetId, $output);
			}
			catch(Exception $e)
			{
				VidiunLog::err("Log conversion: " . $e->getMessage());
			}

			// check scan results
			switch ($data->scanResult)
			{
				case VidiunVirusScanJobResult::SCAN_ERROR:
					$this->closeJob($job, VidiunBatchJobErrorTypes::APP, null, "Error: " . $errorDescription, VidiunBatchJobStatus::RETRY, $data);
					break;
				
				case VidiunVirusScanJobResult::FILE_IS_CLEAN:
					$this->closeJob($job, null, null, "Scan finished - file was found to be clean", VidiunBatchJobStatus::FINISHED, $data);
					break;
				
				case VidiunVirusScanJobResult::FILE_WAS_CLEANED:
					$this->closeJob($job, null, null, "Scan finished - file was infected but scan has managed to clean it", VidiunBatchJobStatus::FINISHED, $data);
					break;
					
				case VidiunVirusScanJobResult::FILE_INFECTED:
				
					$this->closeJob($job, null, null, "File was found INFECTED and wasn't cleaned!", VidiunBatchJobStatus::FINISHED, $data);
					break;
					
				default:
					$data->scanResult = VidiunVirusScanJobResult::SCAN_ERROR;
					$this->closeJob($job, VidiunBatchJobErrorTypes::APP, null, "Error: Emtpy scan result returned", VidiunBatchJobStatus::RETRY, $data);
					break;
			}
			
		}
		catch(Exception $ex)
		{
			$data->scanResult = VidiunVirusScanJobResult::SCAN_ERROR;
			$this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), VidiunBatchJobStatus::FAILED, $data);
		}
		return $job;
	}
}

<?php
/**
 * @package Scheduler
 * @subpackage Copy
 */

/**
 * Will create csv of objects and mail it
 *
 * @package Scheduler
 * @subpackage Export-Csv
 */
class VAsyncExportCsv extends VJobHandlerWorker
{

	private $apiError = null;

	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::EXPORT_CSV;
	}
	/**
	 * (non-PHPdoc)
	 * @see VBatchBase::getJobType()
	 */
	protected function getJobType()
	{
		return VidiunBatchJobType::EXPORT_CSV;
	}

	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->generateCsvForExport($job, $job->data);
	}

	/**
	 * Generate csv contains users info which will be later sent by mail
	 */
	private function generateCsvForExport(VidiunBatchJob $job, VidiunExportCsvJobData $data)
	{
		$this->updateJob($job, "Start generating csv for export", VidiunBatchJobStatus::PROCESSING);
		self::impersonate($job->partnerId);

		// Create local path for csv generation
		$directory = self::$taskConfig->params->localTempPath . DIRECTORY_SEPARATOR . $job->partnerId;
		VBatchBase::createDir($directory);
		$filePath = $directory . DIRECTORY_SEPARATOR . 'export_' .$job->partnerId.'_'.$job->id . '.csv';
		$data->outputPath = $filePath;
		VidiunLog::info("Temp file path: [$filePath]");

		//fill the csv with users data
		$csvFile = fopen($filePath,"w");
		
		$engine = VObjectExportEngine::getInstance($job->jobSubType);
		$engine->fillCsv($csvFile, $data);
		
		fclose($csvFile);
		$this->setFilePermissions($filePath);
		self::unimpersonate();

		if($this->apiError)
		{
			$e = $this->apiError;
			return $this->closeJob($job, VidiunBatchJobErrorTypes::VIDIUN_API, $e->getCode(), $e->getMessage(), VidiunBatchJobStatus::RETRY);
		}

		// Copy the report to shared location.
		$this->moveFile($job, $data, $job->partnerId);
		return $job;
	}


	/**
	 * the function move the file to the shared location
	 */
	protected function moveFile(VidiunBatchJob $job, VidiunExportCsvJobData $data, $partnerId) {
		$fileName =  basename($data->outputPath);
		$directory = self::$taskConfig->params->sharedTempPath . DIRECTORY_SEPARATOR . $partnerId . DIRECTORY_SEPARATOR;
		VBatchBase::createDir($directory);
		$sharedLocation = $directory . $fileName;

		$fileSize = vFile::fileSize($data->outputPath);
		rename($data->outputPath, $sharedLocation);
		$data->outputPath = $sharedLocation;

		$this->setFilePermissions($sharedLocation);
		if(!$this->checkFileExists($sharedLocation, $fileSize))
		{
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, 'Failed to move csv file', VidiunBatchJobStatus::RETRY);
		}

		return $this->closeJob($job, null, null, 'CSV created successfully', VidiunBatchJobStatus::FINISHED, $data);
	}

}


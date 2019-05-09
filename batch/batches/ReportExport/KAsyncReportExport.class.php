<?php
/**
 * @package Scheduler
 * @subpackage ReportExport
 */
class VAsyncReportExport extends VJobHandlerWorker
{

	public static function getType()
	{
		return VidiunBatchJobType::REPORT_EXPORT;
	}

	/**
	 * @param VidiunBatchJob $job
	 * @return VidiunBatchJob
	 */
	protected function exec(VidiunBatchJob $job)
	{
		$this->updateJob($job, 'Creating CSV Export', VidiunBatchJobStatus::PROCESSING);
		$job = $this->createCsv($job, $job->data);
		return $job;
	}

	protected function createCsv(VidiunBatchJob $job, VidiunReportExportJobData $data)
	{
		$partnerId = $job->partnerId;

		$outputDir = self::$taskConfig->params->localTempPath . DIRECTORY_SEPARATOR . $partnerId;
		VBatchBase::createDir($outputDir);

		$reportFiles = array();

		$reportItems = $data->reportItems;
		foreach ($reportItems as $reportItem)
		{
			$engine = ReportExportFactory::getEngine($reportItem, $outputDir);
			if (!$engine)
			{
				return $this->closeJob($job, null, null, 'Report export engine not found', VidiunBatchJobStatus::FAILED, $data);
			}

			try
			{
				VBatchBase::impersonate($job->partnerId);
				$reportFile = $engine->createReport($reportItem);
				VBatchBase::unimpersonate();
				$reportFiles[] = $reportFile;
				$this->setFilePermissions($reportFile);
			}
			catch (Exception $e)
			{
				VBatchBase::unimpersonate();
				return $this->closeJob($job, null, null, 'Cannot create report', VidiunBatchJobStatus::RETRY, $data);
			}
		}

		$this->moveFiles($reportFiles, $job, $data, $partnerId);
		return $job;
	}

	protected function moveFiles($tmpFiles, VidiunBatchJob $job, VidiunReportExportJobData $data, $partnerId)
	{
		VBatchBase::createDir(self::$taskConfig->params->sharedTempPath. DIRECTORY_SEPARATOR . $partnerId);
		$outFiles = array();
		foreach ($tmpFiles as $filePath)
		{
			$res = $this->moveFile($filePath, $partnerId);
			if (!$res)
			{
				return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, 'Failed to move report file', VidiunBatchJobStatus::RETRY);
			}
			$outFiles[] = $res;
		}

		$data->filePaths = implode(',', $outFiles);
		return $this->closeJob($job, null, null, 'CSV files created successfully', VidiunBatchJobStatus::FINISHED, $data);
	}

	protected function moveFile($filePath, $partnerId)
	{
		$fileName =  basename($filePath);
		$sharedLocation = self::$taskConfig->params->sharedTempPath . DIRECTORY_SEPARATOR . $partnerId . DIRECTORY_SEPARATOR . $partnerId . "_" . $fileName;

		$fileSize = vFile::fileSize($filePath);
		rename($filePath, $sharedLocation);

		$this->setFilePermissions($sharedLocation);
		if (!$this->checkFileExists($sharedLocation, $fileSize))
		{
			return false;
		}
		return $sharedLocation;
	}

}

<?php
/**
 * @package Scheduler
 * @subpackage LiveReportExport
 */
class VAsyncLiveReportExport  extends VJobHandlerWorker
{

	public static function getType()
	{
		return VidiunBatchJobType::LIVE_REPORT_EXPORT;
	}

	protected function exec(VidiunBatchJob $job)
	{
		$this->updateJob($job, 'Creating CSV Export', VidiunBatchJobStatus::QUEUED);
		$job = $this->createCsv($job, $job->data);
		return $job;
	}

	protected function createCsv(VidiunBatchJob $job, VidiunLiveReportExportJobData $data) {
		$partnerId =  $job->partnerId;
		$type = $job->jobSubType;
		
		// Create local path for report generation
		$data->outputPath = self::$taskConfig->params->localTempPath . DIRECTORY_SEPARATOR . $partnerId;
		VBatchBase::createDir($data->outputPath);
		
		// Generate report
		VBatchBase::impersonate($job->partnerId);
		$exporter = LiveReportFactory::getExporter($type, $data);
		$reportFile = $exporter->run();
		$this->setFilePermissions($reportFile);
		VBatchBase::unimpersonate();
		
		// Copy the report to shared location.
		$this->moveFile($job, $data, $partnerId);
		
		return $job;
	}
	
	protected function moveFile(VidiunBatchJob $job, VidiunLiveReportExportJobData $data, $partnerId) {
		$fileName =  basename($data->outputPath);
		$sharedLocation = self::$taskConfig->params->sharedPath . DIRECTORY_SEPARATOR . $partnerId . "_" . $fileName;
		
		$fileSize = vFile::fileSize($data->outputPath);
		rename($data->outputPath, $sharedLocation);
		$data->outputPath = $sharedLocation;
		
		$this->setFilePermissions($sharedLocation);
		if(!$this->checkFileExists($sharedLocation, $fileSize))
		{
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, 'Failed to move report file', VidiunBatchJobStatus::RETRY);
		}
	
		return $this->closeJob($job, null, null, 'CSV created successfully', VidiunBatchJobStatus::FINISHED, $data);
	}
	
}

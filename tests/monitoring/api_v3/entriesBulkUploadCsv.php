<?php

define('JOB_STATUS_CODE_OK', 0);
define('JOB_STATUS_CODE_WARNING', 1);
define('JOB_STATUS_CODE_ERROR', 2);

$config = array();
$client = null;
/* @var $client VidiunClient */
require_once __DIR__  . '/common.php';

$options = getopt('', array(
	'service-url:',
	'debug',
));

$monitorResult = new VidiunMonitorResult();
$apiCall = null;

try
{
	$apiCall = 'session.start';
	$start = microtime(true);
	$vs = $client->session->start($config['monitor-partner']['secret'], 'monitor-user', VidiunSessionType::USER, $config['monitor-partner']['id']);
	$client->setVs($vs);
		
	// create add entries csv
	$csvPath = tempnam(sys_get_temp_dir(), 'csv');
	
	$csvData = array(
		array(
			'*title' => 'monitor-bulk-csv1',
			'description' => 'monitor bulk upload csv 1',
			'tags' => 'monitor,csv',
			'url' => $clientConfig->serviceUrl . '/content/templates/entry/data/vidiun_logo_animated_black.flv',
			'contentType' => 'video',
			'category' => 'monitor>csv',
			'thumbnailUrl' => $clientConfig->serviceUrl . '/content/templates/entry/thumbnail/vidiun_logo_animated_black.jpg',
		),
		array(
			'*title' => 'monitor-bulk-csv2',
			'description' => 'monitor bulk upload csv 2',
			'tags' => 'monitor,csv',
			'url' => $clientConfig->serviceUrl . '/content/templates/entry/data/vidiun_logo_animated_blue.flv',
			'contentType' => 'video',
			'category' => 'monitor>csv',
			'thumbnailUrl' => $clientConfig->serviceUrl . '/content/templates/entry/thumbnail/vidiun_logo_animated_blue.jpg',
		),
	);

	$f = fopen($csvPath, 'w');
	fputcsv($f, array_keys(reset($csvData)));
	foreach ($csvData as $csvLine)
		fputcsv($f, $csvLine);
	fclose($f);
	
	$bulkError = null;
	$bulkStatus;
	$apiCall = 'media.bulkUploadAdd';
	$bulkUpload = $client->media->bulkUploadAdd($csvPath);
	/* @var $bulkUpload VidiunBulkUpload */


	$bulkUploadPlugin = VidiunBulkUploadClientPlugin::get($client);
	while($bulkUpload)
	{
		if($bulkUpload->status == VidiunBatchJobStatus::FINISHED)
		{
			$bulkStatus = JOB_STATUS_CODE_OK;
			$monitorDescription = "Entries Bulk Upload Job was finished successfully";
			break;
		}
		if($bulkUpload->status == VidiunBatchJobStatus::FINISHED_PARTIALLY)
		{
			$bulkStatus = JOB_STATUS_CODE_WARNING;
			$monitorDescription = "Entries Bulk Upload Job was finished, but with some errors";
			break;
		}
		if($bulkUpload->status == VidiunBatchJobStatus::FAILED)
		{
			$bulkError =  "Bulk upload [$bulkUpload->id] failed";
			break;
		}
		if($bulkUpload->status == VidiunBatchJobStatus::ABORTED)
		{
			$bulkError = "Bulk upload [$bulkUpload->id] aborted";
			break;
		}
		if($bulkUpload->status == VidiunBatchJobStatus::FATAL)
		{
			$bulkError = "Bulk upload [$bulkUpload->id] failed fataly";
			break;
		}
			
		sleep(15);
		$bulkUpload = $bulkUploadPlugin->bulk->get($bulkUpload->id);
	}

	$end = microtime(true);
	if(!$bulkUpload)
	{
		 $bulkError = "Bulk upload not found";
	}
	
	if ($bulkError) {
		$bulkStatus = JOB_STATUS_CODE_ERROR;
		$error = new VidiunMonitorError();
		$error->description = $bulkError;
		$error->level = VidiunMonitorError::ERR;
	
		$monitorResult->errors[] = $error;
		$monitorDescription = $bulkError;
	}
	
	try
	{
		$apiCall = 'media.list';
		$entriesFilter = new VidiunMediaEntryFilter();
		$entriesFilter->categoriesFullNameIn = 'monitor>csv';
		$entriesPager = new VidiunFilterPager();
		$entriesPager->pageSize = 10;

		$entriesList = $client->media->listAction($entriesFilter, $entriesPager);
		foreach($entriesList->objects as $entry)
		/*VidiunMediaEntry*/
		{
			$apiCall = 'media.delete';
			$client->media->delete($entry->id);
		}
	}
	catch(Exception $ex)
	{
		$error = new VidiunMonitorError();
		$error->code = $ex->getCode();
		$error->description = $ex->getMessage();
		$error->level = VidiunMonitorError::WARN;
		
		$monitorResult->errors[] = $error;
	}

	$monitorResult->executionTime = $end - $start;
	$monitorResult->value = $bulkStatus;
	$monitorResult->description = $monitorDescription;
	
}	
catch(VidiunException $e)
{
	$end = microtime(true);
	$monitorResult->executionTime = $end - $start;
	
	$error = new VidiunMonitorError();
	$error->code = $e->getCode();
	$error->description = $e->getMessage();
	$error->level = VidiunMonitorError::ERR;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = "Exception: " . get_class($e) . ", API: $apiCall, Code: " . $e->getCode() . ", Message: " . $e->getMessage();
}
catch(VidiunClientException $ce)
{
	$end = microtime(true);
	$monitorResult->executionTime = $end - $start;
	
	$error = new VidiunMonitorError();
	$error->code = $ce->getCode();
	$error->description = $ce->getMessage();
	$error->level = VidiunMonitorError::CRIT;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = "Exception: " . get_class($ce) . ", API: $apiCall, Code: " . $ce->getCode() . ", Message: " . $ce->getMessage();
}

echo "$monitorResult";
exit(0);


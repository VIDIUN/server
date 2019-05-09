<?php
$config = array();
$client = null;
/* @var $client VidiunClient */
require_once __DIR__  . '/common.php';

$options = getopt('', array(
	'service-url:',
	'debug',
	'job-type:',
	'job-sub-type:',
));

if(!isset($options['job-type']))
{
	echo "Arguments job-type is required";
	exit(-1);
}
$jobType = $options['job-type'];

 if (!defined("VidiunBatchJobType::$jobType"))
{
	echo "job-type $jobType is not defined";
	exit(-1);
}

$monitorResult = new VidiunMonitorResult();
$apiCall = null;

try
{
	$apiCall = 'session.start';
	$start = microtime(true);
	$vs = $client->session->start($config['batch-partner']['adminSecret'], "",  VidiunSessionType::ADMIN, $config['batch-partner']['id']);
	$client->setVs($vs);
		
	
	$apiCall = 'batch.getQueueSize';
	$workerQueueFilter = new VidiunWorkerQueueFilter();
	$workerQueueFilter->jobType = constant("VidiunBatchJobType::$jobType");
	$batchJobFilter = new VidiunBatchJobFilter();
	if (isset($options['job-sub-type'])) {
		$batchJobFilter->jobSubTypeEqual = $options['job-sub-type'];
	}
	$workerQueueFilter->filter = $batchJobFilter;
	
	$start = microtime(true);
	$queueSize = $client->batch->getQueueSize($workerQueueFilter);
 	$requestEnd =  microtime(true);
	$monitorResult->executionTime = $requestEnd - $start;
	$monitorResult->value =  $queueSize;
	$monitorResult->description = "Scheduler Queue for $jobType is: $monitorResult->value";
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

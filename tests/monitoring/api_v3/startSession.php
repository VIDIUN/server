<?php
$config = array();
$client = null;
/* @var $client VidiunClient */
require_once __DIR__  . '/common.php';

$options = getopt('', array(
	'service-url:',
	'debug',
	'vs-type:',
));

$secretField = 'secret';
if(isset($options['vs-type']) && $options['vs-type'] == 'admin')
	$secretField = 'adminSecret';

$start = microtime(true);
$monitorResult = new VidiunMonitorResult();
$apiCall = null;
try
{
	$apiCall = 'session.start';
	$vs = $client->session->start($config['monitor-partner'][$secretField], 'monitor-user', VidiunSessionType::USER, $config['monitor-partner']['id']);
	$end = microtime(true);
	
	$monitorResult->executionTime = $end - $start;
	$monitorResult->value = $monitorResult->executionTime;
	$monitorResult->description = "Start session execution time: $monitorResult->value seconds";
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

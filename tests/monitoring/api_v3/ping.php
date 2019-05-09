<?php
$client = null;
/* @var $client VidiunClient */
require_once __DIR__  . '/common.php';

$start = microtime(true);
$monitorResult = new VidiunMonitorResult();
try
{
	$res = $client->system->ping();
	$end = microtime(true);

	$monitorResult->executionTime = $end - $start;
	$monitorResult->value = $monitorResult->executionTime;
	$monitorResult->description = "Ping time: $monitorResult->value seconds";
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
	$monitorResult->description = "Exception: " . get_class($e) . ", Code: " . $e->getCode() . ", Message: " . $e->getMessage();
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
	$monitorResult->description = "Exception: " . get_class($ce) . ", Code: " . $ce->getCode() . ", Message: " . $ce->getMessage();
}

echo "$monitorResult";
exit(0);

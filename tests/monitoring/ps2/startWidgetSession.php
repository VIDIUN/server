<?php
$config = array();
$client = null;
/* @var $client VidiunMonitorClientPs2 */
require_once __DIR__  . '/common.php';

$start = microtime(true);
$monitorResult = new VidiunMonitorResult();
try
{
	$params = array(
		'partner_id' => $config['monitor-partner']['id'],
		'widget_id' => $config['monitor-partner']['widgetId'],
	);
	
	$response = $client->request('startwidgetsession', $params);
	if(!isset($response['result']) || !isset($response['result']['vs']))
		throw new Exception("no vs returned");

	$monitorResult->executionTime = microtime(true) - $start;
	$monitorResult->value = $monitorResult->executionTime;
	$monitorResult->description = "Start session execution time: $monitorResult->value seconds";
}
catch(Exception $e)
{
	$monitorResult->executionTime = microtime(true) - $start;
	
	$error = new VidiunMonitorError();
	$error->code = $e->getCode();
	$error->description = $e->getMessage();
	$error->level = VidiunMonitorError::CRIT;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = "Exception: " . get_class($e) . ", Code: " . $e->getCode() . ", Message: " . $e->getMessage();
}

echo "$monitorResult";
exit(0);

<?php
$config = array();
$client = null;
$serviceUrl = null;
/* @var $client VidiunClient */
require_once __DIR__  . '/common.php';

$options = getopt('', array(
	'service-url:',
	'debug',
	'timeout:',
	'media-url:',
));

if(!isset($options['timeout']))
{
	echo "Argument timeout is required";
	exit(-1);
}
$timeout = $options['timeout'];

$mediaUrl = $serviceUrl . '/content/templates/entry/data/vidiun_logo_animated_blue.flv';
if(isset($options['media-url']))
	$mediaUrl = $options['media-url'];

$start = microtime(true);
$monitorResult = new VidiunMonitorResult();
$apiCall = null;
try
{
	$apiCall = 'session.start';
	$vs = $client->session->start($config['monitor-partner']['secret'], 'monitor-user', VidiunSessionType::USER, $config['monitor-partner']['id']);
	$client->setVs($vs);
		
	 // Creates a new entry
	$entry = new VidiunMediaEntry();
	$entry->name = 'monitor-test';
	$entry->description = 'monitor-test';
	$entry->mediaType = VidiunMediaType::VIDEO;
	
	$resource = new VidiunUrlResource();
	$resource->url = $mediaUrl;
	
	$apiCall = 'multirequest';
	$client->startMultiRequest();
	$requestEntry = $client->media->add($entry);
	/* @var $requestEntry VidiunMediaEntry */
	$client->media->addContent($requestEntry->id, $resource);
	$client->media->get($requestEntry->id);
	
	$results = $client->doMultiRequest();
	foreach($results as $index => $result)
	{
		if ($client->isError($result))
			throw new VidiunException($result["message"], $result["code"]);
	}
		
	// Waits for the entry to start conversion
	$createdEntry = end($results);
	$timeoutTime = time() + $timeout;
	/* @var $createdEntry VidiunMediaEntry */
	while ($createdEntry)
	{
		if(time() > $timeoutTime)
			throw new Exception("timed out, entry id: $createdEntry->id");
			
		if($createdEntry->status == VidiunEntryStatus::IMPORT)
		{
			sleep(1);
			$apiCall = 'media.get';
			$createdEntry = $client->media->get($createdEntry->id);
			continue;
		}
		
		$monitorResult->executionTime = microtime(true) - $start;
		$monitorResult->value = $monitorResult->executionTime;
		
		if($createdEntry->status == VidiunEntryStatus::READY || $createdEntry->status == VidiunEntryStatus::PRECONVERT)
		{
			$monitorResult->description = "import time: $monitorResult->executionTime seconds";
		}
		elseif($createdEntry->status == VidiunEntryStatus::ERROR_IMPORTING)
		{
			$error = new VidiunMonitorError();
			$error->description = "import failed, entry id: $createdEntry->id";
			$error->level = VidiunMonitorError::CRIT;
			
			$monitorResult->errors[] = $error;
			$monitorResult->description = "import failed, entry id: $createdEntry->id";
		}
		else
		{
			$error = new VidiunMonitorError();
			$error->description = "unexpected entry status: $createdEntry->status, entry id: $createdEntry->id";
			$error->level = VidiunMonitorError::CRIT;
			
			$monitorResult->errors[] = $error;
			$monitorResult->description = "unexpected entry status: $createdEntry->status, entry id: $createdEntry->id";
		}
		
		break;
	}

	try
	{
		$apiCall = 'media.delete';
		$createdEntry = $client->media->delete($createdEntry->id);
	}
	catch(Exception $ex)
	{
		$error = new VidiunMonitorError();
		$error->code = $ex->getCode();
		$error->description = $ex->getMessage();
		$error->level = VidiunMonitorError::WARN;
		
		$monitorResult->errors[] = $error;
	}
}
catch(VidiunException $e)
{
	$monitorResult->executionTime = microtime(true) - $start;
	
	$error = new VidiunMonitorError();
	$error->code = $e->getCode();
	$error->description = $e->getMessage();
	$error->level = VidiunMonitorError::ERR;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = "Exception: " . get_class($e) . ", API: $apiCall, Code: " . $e->getCode() . ", Message: " . $e->getMessage();
}
catch(VidiunClientException $ce)
{
	$monitorResult->executionTime = microtime(true) - $start;
	
	$error = new VidiunMonitorError();
	$error->code = $ce->getCode();
	$error->description = $ce->getMessage();
	$error->level = VidiunMonitorError::CRIT;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = "Exception: " . get_class($ce) . ", API: $apiCall, Code: " . $ce->getCode() . ", Message: " . $ce->getMessage();
}
catch(Exception $ex)
{
	$monitorResult->executionTime = microtime(true) - $start;
	
	$error = new VidiunMonitorError();
	$error->code = $ex->getCode();
	$error->description = $ex->getMessage();
	$error->level = VidiunMonitorError::ERR;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = $ex->getMessage();
}

echo "$monitorResult";
exit(0);

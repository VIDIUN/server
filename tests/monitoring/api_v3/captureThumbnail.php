<?php
$config = array();
$client = null;
$serviceUrl = null;
/* @var $client VidiunClient */
require_once __DIR__  . '/common.php';

$options = getopt('', array(
	'service-url:',
	'debug',
	'entry-id:',
	'entry-reference-id:',
));

$start = microtime(true);
$monitorResult = new VidiunMonitorResult();
$apiCall = null;
try
{
	$apiCall = 'session.start';
	$vs = $client->session->start($config['monitor-partner']['adminSecret'], 'monitor-user', VidiunSessionType::ADMIN, $config['monitor-partner']['id']);
	$client->setVs($vs);
	
	$entry = null;
	/* @var $entry VidiunMediaEntry */
	if(isset($options['entry-id']))
	{
		$apiCall = 'media.get';
		$entry = $client->media->get($options['entry-id']);
	}
	elseif(isset($options['entry-reference-id']))
	{
		$apiCall = 'baseEntry.listByReferenceId';
		$baseEntryList = $client->baseEntry->listByReferenceId($options['entry-reference-id']);
		/* @var $baseEntryList VidiunBaseEntryListResponse */
		if(!count($baseEntryList->objects))
			throw new Exception("Entry with reference id [" . $options['entry-reference-id'] . "] not found");
			
		$entry = reset($baseEntryList->objects);
	}
	
	if($entry->status != VidiunEntryStatus::READY)
		throw new Exception("Entry id [$entry->id] is not ready for thumbnail capturing");
	
	$thumbParams = new VidiunThumbParams();
	$thumbParams->videoOffset = 3;
	
	$apiCall = 'thumbAsset.generate';
	$thumbAsset = $client->thumbAsset->generate($entry->id, $thumbParams);
	/* @var $thumbAsset VidiunThumbAsset */
	if(!$thumbAsset)
		throw new Exception("thumbnail asset not created");
	
	$monitorResult->executionTime = microtime(true) - $start;
	$monitorResult->value = $monitorResult->executionTime;
	
	if($thumbAsset->status == VidiunThumbAssetStatus::READY || $thumbAsset->status == VidiunThumbAssetStatus::EXPORTING)
	{
		$monitorResult->description = "capture time: $monitorResult->executionTime seconds";
	}
	elseif($thumbAsset->status == VidiunThumbAssetStatus::ERROR)
	{
		$error = new VidiunMonitorError();
		$error->description = "captura failed, asset id, $thumbAsset->id: $thumbAsset->description";
		$error->level = VidiunMonitorError::CRIT;
		
		$monitorResult->description = "captura failed, asset id, $thumbAsset->id";
	}
	else
	{
		$error = new VidiunMonitorError();
		$error->description = "unexpected thumbnail status, $thumbAsset->status, asset id, $thumbAsset->id: $thumbAsset->description";
		$error->level = VidiunMonitorError::CRIT;
		
		$monitorResult->errors[] = $error;
		$monitorResult->description = "unexpected thumbnail status, $thumbAsset->status, asset id, $thumbAsset->id: $thumbAsset->description";
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

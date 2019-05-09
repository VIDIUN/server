<?php
$config = array();
$client = null;
/* @var $client VidiunClient */
require_once __DIR__  . '/common.php';

$options = getopt('', array(
	'service-url:',
	'debug',
	'entry-id:',
	'entry-reference-id:',
	'list-flavors',
	'list-cue-points',
	'list-metadata',
));

if(!isset($options['entry-id']) && !isset($options['entry-reference-id']))
{
	echo "One of arguments entry-id or entry-reference-id is required";
	exit(-1);
}

$start = microtime(true);
$monitorResult = new VidiunMonitorResult();
$apiCall = null;
try
{
	$apiCall = 'session.start';
	$vs = $client->session->start($config['monitor-partner']['secret'], 'monitor-user', VidiunSessionType::USER, $config['monitor-partner']['id']);
	$client->setVs($vs);
		
	$entryId = null;

	$contextDataParams = new VidiunEntryContextDataParams();
	$contextDataParams->streamerType = 'http';
	
	$client->startMultiRequest();

	if(isset($options['entry-id']))
	{
		$entry = $client->baseEntry->get($options['entry-id']);
		/* @var $entry VidiunMediaEntry */
	}
	elseif(isset($options['entry-reference-id']))
	{
		$baseEntryList = $client->baseEntry->listByReferenceId($options['entry-reference-id']);
		/* @var $baseEntryList VidiunBaseEntryListResponse */
		$entry = $baseEntryList->objects[0];
		/* @var $entry VidiunMediaEntry */
	}
	
	$client->baseEntry->getContextData($entry->id, $contextDataParams);
	
	if(isset($options['list-flavors']))
	{
		$flavorAssetFilter = new VidiunFlavorAssetFilter();
		$flavorAssetFilter->entryIdEqual = $entry->id;
		$flavorAssetFilter->statusEqual = VidiunFlavorAssetStatus::READY;
		$client->flavorAsset->listAction($flavorAssetFilter);
	}
	
	if(isset($options['list-cue-points']))
	{
		$cuePointFilter = new VidiunCuePointFilter();
		$cuePointFilter->entryIdEqual = $entry->id;
		$cuePointFilter->statusEqual = VidiunCuePointStatus::READY;
		$cuePointPlugin = VidiunCuePointClientPlugin::get($client);
		$cuePointPlugin->cuePoint->listAction($cuePointFilter);
	}
	
	if(isset($options['list-metadata']))
	{
		$metadataFilter = new VidiunMetadataFilter();
		$metadataFilter->entryIdEqual = $entry->id;
		$metadataFilter->statusEqual = VidiunMetadataStatus::VALID;
		$metadataPlugin = VidiunMetadataClientPlugin::get($client);
		$metadataPlugin->metadata->listAction($metadataFilter);
	}

	$requestStart = microtime(true);
	$apiCall = 'multi-request';
	$responses = $client->doMultiRequest();
	$requestEnd = microtime(true);
	
	foreach($responses as $response)
	{
		if(is_array($response) && isset($response['message']) && isset($response['code']))
			throw new VidiunException($response["message"], $response["code"]);
	}
	
	$monitorResult->executionTime = $requestEnd - $start;
	$monitorResult->value = $requestEnd - $requestStart;
	$monitorResult->description = "Multi-request execution time: $monitorResult->value seconds";
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

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
	'conversion-profile-id:',
	'conversion-profile-system-name:',
	'use-single-resource',
	'use-multi-request',
));

if(!isset($options['timeout']))
{
	echo "Argument timeout is required";
	exit(-1);
}
$timeout = $options['timeout'];

if(!isset($options['conversion-profile-id']) && !isset($options['conversion-profile-system-name']))
{
	echo "One of arguments conversion-profile-id or conversion-profile-system-name is required";
	exit(-1);
}

$start = microtime(true);
$monitorResult = new VidiunMonitorResult();
$apiCall = null;
try
{
	$conversionProfileId = null;
	/* @var $entry VidiunMediaEntry */
	if(isset($options['conversion-profile-id']))
	{
		$conversionProfileId = $options['conversion-profile-id'];
	}
	elseif(isset($options['conversion-profile-system-name']))
	{
		$apiCall = 'session.start';
		$vs = $client->session->start($config['monitor-partner']['adminSecret'], 'monitor-user', VidiunSessionType::ADMIN, $config['monitor-partner']['id']);
		$client->setVs($vs);
			
		$conversionProfileFilter = new VidiunConversionProfileFilter();
		$conversionProfileFilter->systemNameEqual = $options['conversion-profile-system-name'];
		
		$apiCall = 'conversionProfile.list';
		$conversionProfileList = $client->conversionProfile->listAction($conversionProfileFilter);
		/* @var $conversionProfileList VidiunConversionProfileListResponse */
		if(!count($conversionProfileList->objects))
			throw new Exception("conversion profile with system name [" . $options['conversion-profile-system-name'] . "] not found");
			
		$conversionProfile = reset($conversionProfileList->objects);
		/* @var $conversionProfile VidiunConversionProfile */
		$conversionProfileId = $conversionProfile->id;
	}

	$apiCall = 'session.start';
	$vs = $client->session->start($config['monitor-partner']['secret'], 'monitor-user', VidiunSessionType::USER, $config['monitor-partner']['id']);
	$client->setVs($vs);
	
	$flavors = array(
		0 => __DIR__ . '/media/source.mp4',
		1 => __DIR__ . '/media/flavor1.3gp',
		2 => __DIR__ . '/media/flavor2.mp4',
		3 => __DIR__ . '/media/flavor3.mp4',
	);
	
	if(isset($options['use-multi-request']))
		$client->startMultiRequest();
		
	 // Creates a new entry
	$entry = new VidiunMediaEntry();
	$entry->name = 'monitor-test';
	$entry->description = 'monitor-test';
	$entry->mediaType = VidiunMediaType::VIDEO;
	
	$apiCall = 'media.add';
	$createdEntry = $client->media->add($entry);
	/* @var $createdEntry VidiunMediaEntry */
	
	$resources = array();
	foreach($flavors as $assetParamsId => $filePath)
	{
		$uploadToken = new VidiunUploadToken();
		$uploadToken->fileName = basename($filePath);
		$uploadToken->fileSize = filesize($filePath);
		
		$createdToken = $client->uploadToken->add($uploadToken);
		/* @var $createdToken VidiunUploadToken */
		$uploadedToken = $client->uploadToken->upload($createdToken->id, $filePath);
		/* @var $uploadedToken VidiunUploadToken */
		
		$contentResource = new VidiunUploadedFileTokenResource();
		$contentResource->token = $uploadedToken->id;
		
		$resources[$assetParamsId] = $contentResource;
	}
	
	if(isset($options['use-single-resource']))
	{
		$resource = new VidiunAssetsParamsResourceContainers();
		$resource->resources = array();
		
		foreach($resources as $assetParamsId => $contentResource)
		{
			$flavorResource = new VidiunAssetParamsResourceContainer();
			$flavorResource->assetParamsId = $assetParamsId;
			$flavorResource->resource = $contentResource;
			
			$resource->resources[] = $flavorResource;
		}
		$client->media->addContent($createdEntry->id, $resource);
	}
	else
	{
		foreach($resources as $flavorParamsId => $contentResource)
		{
			$flavorAsset = new VidiunFlavorAsset();
			$flavorAsset->flavorParamsId = $flavorParamsId;
			$createdAsset = $client->flavorAsset->add($createdEntry->id, $flavorAsset);
			/* @var $createdAsset VidiunFlavorAsset */
			
			$client->flavorAsset->setContent($createdAsset->id, $contentResource);
		}
	}
	// Waits for the entry to start conversion
	$apiCall = 'media.get';
	$createdEntry = $client->media->get($createdEntry->id);
	
	if(isset($options['use-multi-request']))
	{
		$apiCall = 'multirequest';
		$results = $client->doMultiRequest();
		foreach($results as $index => $result)
		{
			if ($client->isError($result))
				throw new VidiunException($result["message"], $result["code"]);
		}
		
		$createdEntry = end($results);
	}
	
	$timeoutTime = time() + $timeout;
	/* @var $createdEntry VidiunMediaEntry */
	while ($createdEntry)
	{
		if(time() > $timeoutTime)
			throw new Exception("timed out, entry id: $createdEntry->id");
			
		if($createdEntry->status == VidiunEntryStatus::PRECONVERT)
		{
			sleep(1);
			$apiCall = 'media.get';
			$createdEntry = $client->media->get($createdEntry->id);
			continue;
		}
		
		$monitorResult->executionTime = microtime(true) - $start;
		$monitorResult->value = $monitorResult->executionTime;
		
		if($createdEntry->status == VidiunEntryStatus::READY)
		{
			$monitorResult->description = "ingestion time: $monitorResult->executionTime seconds";
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

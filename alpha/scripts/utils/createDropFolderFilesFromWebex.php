<?php
require_once(__DIR__ . '/../bootstrap.php');
require_once('/opt/vidiun/web/content/clientlibs/batchClient/VidiunClient.php');
require_once('/opt/vidiun/web/content/clientlibs/batchClient/VidiunPlugins/VidiunDropFolderClientPlugin.php');
require_once('/opt/vidiun/app/batch/batches/VBatchBase.class.php');

const WEEK_IN_SECONDS = 604800;
if($argc < 7)
{
	echo "Missing arguments.\n";
	echo "php $argv[0] {dropFolderId} {admin vs} {serviceUrl} {start date} {end date} {log filename}.\n";
	echo "{start date} and {end date} are in seconds.\n";
	die;
}

/**
 * @param $startDate
 * @param $endDate
 * @param webexWrapper $webexWrapper
 * @param $serviceTypes
 * @param $logFileName
 * @return array
 */
function getRecordingsFile($startDate, $endDate, $webexWrapper, $serviceTypes, $logFileName)
{
	$startTime = date('m/j/Y H:i:s', $startDate);
	$endTimeEpoch = min($startDate+WEEK_IN_SECONDS, $endDate);
	$endTime = date('m/j/Y H:i:s', $endTimeEpoch);
	$result = $webexWrapper->listAllRecordings($serviceTypes, $startTime, $endTime);
	if($result)
	{
		$numOfFiles = count($result);
		$text = "Found {$numOfFiles} of files for {$startTime}-{$endTime}.";
		file_put_contents($logFileName, $text, FILE_APPEND );
		VidiunLog::debug($text);
	}
	else
	{
		VidiunLog::debug("No files found for {$startTime}-{$endTime}.");
	}

	return $result;
}

/**
 * @param $files
 * @param $logFileName
 * @param VWebexDropFolderEngine $webexEngine
 */
function handleFiles($files, $logFileName, $webexEngine)
{
	$handleResult = $webexEngine->HandleNewFiles($files);
	file_put_contents($logFileName, $handleResult->toString(), FILE_APPEND );
}

$dropFolderId = $argv[1];
$vs =  $argv[2];
$url = $argv[3];
$startDate = $argv[4];
$endDate = $argv[5];
$logFileName = $argv[6];
$config = new VidiunConfiguration(-2);
$config->serviceUrl = $url;
$client = new VidiunClient($config);
$client->setVs($vs);
$dropFolderPlugin = VidiunDropFolderClientPlugin::get($client);
VBatchBase::$vClient = $client;
$dropFolder = $dropFolderPlugin->dropFolder->get($dropFolderId);
VBatchBase::impersonate($dropFolder->partnerId);
$webexEngine = new VWebexDropFolderEngine();
$webexEngine->setDropFolder($dropFolder);
$securityContext = $webexEngine->getWebexClientSecurityContext($dropFolder);
$dropFolderServiceTypes = $dropFolder->webexServiceType ? explode(',', $dropFolder->webexServiceType) :
	array(WebexXmlComServiceTypeType::_MEETINGCENTER);
$serviceTypes = webexWrapper::stringServicesTypesToWebexXmlArray($dropFolderServiceTypes);
$webexWrapper = new webexWrapper($dropFolder->webexServiceUrl . '/' . $dropFolder->path, $securityContext, array('VidiunLog', 'err'),
	array('VidiunLog', 'debug'), false);
for ($i = $startDate; $i < $endDate; $i = $i + WEEK_IN_SECONDS)
{
	$files = getRecordingsFile($i, $endDate, $webexWrapper, $serviceTypes, $logFileName);
	if ($files)
	{
		file_put_contents($logFileName, "Starting to handle files:" . PHP_EOL, FILE_APPEND);
		handleFiles($files, $logFileName, $webexEngine);
	}
}


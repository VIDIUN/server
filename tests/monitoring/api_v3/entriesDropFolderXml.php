<?php

define('JOB_STATUS_CODE_OK', 0);
define('JOB_STATUS_CODE_WARNING', 1);
define('JOB_STATUS_CODE_ERROR', 2);
define('TOKEN_CHAR', '@');
define('BULK_XML_FILE_ADD', '/xml/entries_drop_folder.xml');
define('DROP_FOLDER_DIR', '/dropfolders/monitor');
define('SYNC_DROP_FOLDER_SCRIPT', '/plugins/drop_folder/external_script/SyncDropFolderWatcher.php');
define('DETECTED_ACTION', 1);

function replaceTokensInString($string, $values)
{
		foreach($values as $key => $var)
		{
			if(is_array($var))
				continue;

			$key = TOKEN_CHAR . $key . TOKEN_CHAR;
			$string = str_replace($key, $var, $string);
		}
		return $string;
}

$config = array();
$client = null;
/* @var $client VidiunClient */
require_once __DIR__  . '/common.php';

$options = getopt('', array(
	'service-url:',
	'debug',
	'timeout:',
));

if(!isset($options['timeout']))
{
	echo "Argument timeout is required";
	exit(-1);
}
$timeout = $options['timeout'];

$monitorResult = new VidiunMonitorResult();
$apiCall = null;

try
{
	$apiCall = 'session.start';
	$start = microtime(true);
	$vs = $client->session->start($config['monitor-partner']['adminSecret'], 'monitor-user', VidiunSessionType::ADMIN, $config['monitor-partner']['id']);
	$client->setVs($vs);
	
	$web_dir = $config['path']['web_dir'];
	$app_dir = $config['path']['app_dir'];
	
	$entry1_file = uniqid('entry1_file') . '.flv';
	$entry2_file = uniqid('entry2_file') . '.flv';
	$entry1_path = $web_dir . DROP_FOLDER_DIR . "/" .  $entry1_file;
	$entry2_path = $web_dir . DROP_FOLDER_DIR . "/" .  $entry2_file;
	copy($web_dir . '/content/templates/entry/data/vidiun_logo_animated_green.flv' , $entry1_path);
	copy($web_dir . '/content/templates/entry/data/vidiun_logo_animated_blue.flv' , $entry2_path);
	$entry1_filesize = filesize($entry1_path);
	$entry2_filesize = filesize($entry2_path);
	
	$data = @file_get_contents(__DIR__ . BULK_XML_FILE_ADD);
	$entries_data = array(
		'ENTRY1_FILEPATH' => $entry1_file,
		'ENTRY1_FILESIZE' => $entry1_filesize,
		'ENTRY2_FILEPATH' => $entry2_file,
		'ENTRY2_FILESIZE' => $entry2_filesize,
	);
	
	$xml = replaceTokensInString($data, $entries_data);
	$bulkXmlFileName = uniqid('bulk_upload') . '.xml';
	$xmlPath =  $web_dir . DROP_FOLDER_DIR . "/" . $bulkXmlFileName;
	file_put_contents($xmlPath, $xml);
	$xml_filesize = filesize($xmlPath);

	/*
	$php_bin = $config['path']['php_bin'];
	$syncDropFolderCommand = $app_dir . SYNC_DROP_FOLDER_SCRIPT . ' ' . DETECTED_ACTION . ' $xmlPath  $xml_filesize'; 
	exec($php_bin . ' ' . $syncDropFolderCommand);
	$syncDropFolderCommand = $app_dir . SYNC_DROP_FOLDER_SCRIPT . ' ' . DETECTED_ACTION . ' $entry1_path $entry1_filesize'; 
	exec($php_bin . ' ' . $syncDropFolderCommand);
	$syncDropFolderCommand = $app_dir . SYNC_DROP_FOLDER_SCRIPT . ' ' . DETECTED_ACTION . ' $entry2_path $entry2_filesize'; 
	exec($php_bin . ' ' . $syncDropFolderCommand);
	*/
	
	
	$dropFolderPlugin = VidiunDropFolderClientPlugin::get($client);
	$dropFolderFileFilter = new VidiunDropFolderFileFilter();
	$dropFolderFileFilter->fileNameEqual = $bulkXmlFileName;
	$dropFolderFilesPager = new VidiunFilterPager();
	$dropFolderFilesPager->pageSize = 1;
	
	$dropFolderFilesList = $dropFolderPlugin->dropFolderFile->listAction($dropFolderFileFilter, $dropFolderFilesPager);
	$dropFolderFile = reset($dropFolderFilesList->objects);
	
	$timeoutTime = time() + $timeout;
	while (!$dropFolderFile)
	{
		if(time() > $timeoutTime)
			throw new Exception("timed out, drop folder file not found");
			
		sleep(15);
		$dropFolderFilesList = $dropFolderPlugin->dropFolderFile->listAction($dropFolderFileFilter, $dropFolderFilesPager);
		$dropFolderFile = reset($dropFolderFilesList->objects);
	}

	$dropFolderStatus = null;
	$dropFolderError = null;
	while($dropFolderFile)
	{
		if(time() > $timeoutTime)
			throw new Exception("timed out, drop folder file id: $dropFolderFile->id");
			
		if($dropFolderFile->status == VidiunDropFolderFileStatus::HANDLED ||
			$dropFolderFile->status == VidiunDropFolderFileStatus::PURGED)
		{
			$dropFolderStatus = JOB_STATUS_CODE_OK;
			$monitorDescription = "Drop Folder Ingestion was finished successfully";
			break;
		}
		
		if($dropFolderFile->status == VidiunDropFolderFileStatus::ERROR_HANDLING)
		{
			$dropFolderError =  "Drop Folder File [$dropFolderFile->id] failed";
			break;
		}
			
		sleep(15);
		$dropFolderFile = $dropFolderPlugin->dropFolderFile->get($dropFolderFile->id);
		
	}

	$end = microtime(true);
	if(!$dropFolderFile)
	{
		 $dropFolderError = "Drop Folder file not found";
	}
	
	if ($dropFolderError) {
		$dropFolderStatus = JOB_STATUS_CODE_ERROR;
		$error = new VidiunMonitorError();
		$error->description = $dropFolderError;
		$error->level = VidiunMonitorError::ERR;
	
		$monitorResult->errors[] = $error;
		$monitorDescription = $dropFolderError;
	}
	
	try
	{
		$apiCall = 'media.list';
		$entriesFilter = new VidiunMediaEntryFilter();
		$entriesFilter->categoriesFullNameIn = 'monitor>drop_folder';
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
	$monitorResult->value = $dropFolderStatus;
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


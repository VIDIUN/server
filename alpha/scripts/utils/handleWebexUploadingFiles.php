<?php
require_once(__DIR__ . '/../bootstrap.php');
require_once('/opt/vidiun/web/content/clientlibs/batchClient/VidiunClient.php');
require_once('/opt/vidiun/web/content/clientlibs/batchClient/VidiunPlugins/VidiunDropFolderClientPlugin.php');
require_once('/opt/vidiun/app/batch/batches/VBatchBase.class.php');

if($argc < 5)
{
	echo "Missing arguments.\n";
	echo "php $argv[0] {dropFolderId} {admin vs} {serviceUrl} {log filename}.\n";
	die;
}


$dropFolderId = $argv[1];
$vs =  $argv[2];
$url = $argv[3];
$logFileName = $argv[4];
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
$webexEngine->handleUploadingFiles();



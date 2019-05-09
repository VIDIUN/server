<?php

require_once(dirname(__FILE__).'/../bootstrap.php');

define('MAX_FILESYNC_ID_PREFIX', 'fileSyncMaxId-dc');

if ($argc >= 3)
{
	$targetDc = intval($argv[1]);
	$sourceDc = intval($argv[2]);
}
else
{
	$sourceDc = vDataCenterMgr::getCurrentDcId();
	$targetDc = 1 - $sourceDc;
}

$keysCache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_QUERY_CACHE_KEYS . $targetDc);
if (!$keysCache)
{
	VidiunLog::log('failed to get keys cache');
	exit(1);
}

$maxId = $keysCache->get(MAX_FILESYNC_ID_PREFIX . $sourceDc);
if (!$maxId)
{
	VidiunLog::log('failed to get max file sync id');
	exit(1);
}

$fileSync = FileSyncPeer::retrieveByPK($maxId);
if (!$fileSync)
{
	VidiunLog::log('failed to get the file sync');
	exit(1);
}

echo "The lag of dc $targetDc from dc $sourceDc is: " . (time() - $fileSync->getCreatedAt(null)) . "\n";

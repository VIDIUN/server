<?php

function setCacheExpiry($entriesCount , $feedId)
{
	$expiryArr = vConf::hasMap("v3cache_getfeed_expiry") ? vConf::getMap("v3cache_getfeed_expiry") : array();
	foreach($expiryArr as $item)
	{
		if ($item["key"] == "partnerId" && $item["value"] == vCurrentContext::$partner_id ||
			$item["key"] == "feedId" && $item["value"] == $feedId)
		{
			VidiunResponseCacher::setExpiry($item["expiry"]);
			return;
		}
	}

	$expiry = vConf::get("v3cache_getfeed_default_cache_time_frame" , 'local' , 86400);

	if(vConf::hasParam("v3cache_getfeed_short_limits_array"))
		$shortLimits = vConf::get("v3cache_getfeed_short_limits_array");
	else
		$shortLimits = array(50 => 900 , 100 => 1800 , 200 => 3600 , 400 => 7200);

	foreach ($shortLimits as $numOfEntries => $cacheTimeFrame)
	{
		if ($entriesCount <= $numOfEntries)
		$expiry = min($expiry , $cacheTimeFrame);
	}

	VidiunResponseCacher::setExpiry($expiry);
}

function getRequestParameter($paramName)
{
	if (isset($_GET[$paramName]))
		return $_GET[$paramName];

	// try lowercase
	$paramName = strtolower($paramName);
	if (isset($_GET[$paramName]))
		return $_GET[$paramName];
	
	return null;
}

require_once(__DIR__ . "/../bootstrap.php");

if(!getRequestParameter('feedId'))
	VExternalErrors::dieError(VExternalErrors::INVALID_FEED_ID, 'feedId not supplied');
	
ini_set( "memory_limit" , "256M" );
$start = microtime(true);
set_time_limit(0);

// check cache before loading anything
require_once(__DIR__ . "/../lib/VidiunResponseCacher.php");
$expiry = vConf::hasParam("v3cache_getfeed_default_expiry") ? vConf::get("v3cache_getfeed_default_expiry") : 86400;
$cache = new VidiunResponseCacher(null, vCacheManager::CACHE_TYPE_API_V3_FEED, $expiry);
$cache->checkOrStart();
ob_start();

// Database
DbManager::setConfig(vConf::getDB());
DbManager::initialize();

VidiunLog::debug(">------------------------------------- syndicationFeedRenderer -------------------------------------");
VidiunLog::debug("getFeed Params [" . print_r(requestUtils::getRequestParams(), true) . "]");

vCurrentContext::$host = (isset($_SERVER["HOSTNAME"]) ? $_SERVER["HOSTNAME"] : null);
vCurrentContext::$user_ip = requestUtils::getRemoteAddress();
vCurrentContext::$ps_vesion = "ps3";

$feedId = getRequestParameter('feedId');
$entryId = getRequestParameter('entryId');
$limit = getRequestParameter('limit');
$vs = getRequestParameter('vs');
$state = getRequestParameter('state');

$feedProcessingKey = "feedProcessing_{$feedId}_{$entryId}_{$limit}";
if (function_exists('apc_fetch'))
{
	if (apc_fetch($feedProcessingKey))
	{
		VExternalErrors::dieError(VExternalErrors::PROCESSING_FEED_REQUEST);
	}
}

try
{
	$syndicationFeedRenderer = new VidiunSyndicationFeedRenderer($feedId, $feedProcessingKey, $vs, $state);
	$syndicationFeedRenderer->addFlavorParamsAttachedFilter();
	
	vCurrentContext::$partner_id = $syndicationFeedRenderer->syndicationFeed->partnerId;
	
	if (isset($entryId))
		$syndicationFeedRenderer->addEntryAttachedFilter($entryId);
		
	$syndicationFeedRenderer->execute($limit);
}
catch(PropelException $pex)
{
	VidiunLog::alert($pex->getMessage());
	VExternalErrors::dieError(VExternalErrors::PROCESSING_FEED_REQUEST, 'VidiunSyndication: Database error');
}
catch(Exception $ex)
{
	VidiunLog::err($ex->getMessage());
	$msg = 'VidiunSyndication: ' . str_replace(array("\n", "\r"), array("\t", ''), $ex->getMessage());
	VExternalErrors::dieError(VExternalErrors::PROCESSING_FEED_REQUEST, $msg);
}

//in VidiunSyndicationFeedRenderer - if the limit does restrict the amount of entries - the entries counter passes the limit's value by one , so it must be decreased back
$entriesCount = $syndicationFeedRenderer->getReturnedEntriesCount();
$entriesCount--;

setCacheExpiry($entriesCount , $feedId);

$end = microtime(true);
VidiunLog::info("syndicationFeedRenderer-end [".($end - $start)."] memory: ".memory_get_peak_usage(true));
VidiunLog::debug("<------------------------------------- syndicationFeedRenderer -------------------------------------");

$result = ob_get_contents();
ob_end_clean();
$cache->end($result);

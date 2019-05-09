<?php
$config = null;
$clientConfig = null;
/* @var $clientConfig VidiunConfiguration */
$client = null;
/* @var $client VidiunClient */

require_once __DIR__ . '/lib/init.php';
echo "Test started [" . __FILE__ . "]\n";


$logrotate = $config['dwh']['logRotateBin'];
$kitchen_script= $config['dwh']['kitchenScript'];
$appDir = $config['global']['appDir'];
$dwhDir = $config['dwh']['baseDir'];
$vidiunUser = $config['os']['vidiunUser'];


/**
 * Start a new session
 */
$partnerId = $config['session']['partnerId'];
$adminSecretForSigning = $config['session']['adminSecret'];
$client->setVs($client->generateSessionV2($adminSecretForSigning, 'sanity-user', VidiunSessionType::ADMIN, $partnerId, 86400, ''));
echo "Session started\n";




/**
 * List players
 */
$playersFilter = new VidiunUiConfFilter();
$playersFilter->objTypeIn = VidiunUiConfObjType::PLAYER_V3 . ',' . VidiunUiConfObjType::PLAYER;
$playersFilter->orderBy = VidiunUiConfOrderBy::CREATED_AT_DESC;

$playersPager = new VidiunFilterPager();
$playersPager->pageSize = 1;

$playersList = $client->uiConf->listAction($playersFilter, $playersPager);
/* @var $playersList VidiunUiConfListResponse */

if(!$playersList || !$playersList->totalCount || !count($playersList->objects))
{
	echo "No player found\n";
	exit(-1);
}
$player = reset($playersList->objects);
/* @var $player VidiunUiConf */
echo "Found player ui-conf [$player->id]\n";




/**
 * List ready media entries
 */
$entriesFilter = new VidiunMediaEntryFilter();
$entriesFilter->mediaTypeEqual = VidiunMediaType::VIDEO;
$entriesFilter->statusEqual = VidiunEntryStatus::READY;
$entriesFilter->orderBy = VidiunMediaEntryOrderBy::DURATION_ASC;

$entriesPager = new VidiunFilterPager();
$entriesPager->pageSize = 1;

$entriesList = $client->media->listAction($entriesFilter, $entriesPager);
/* @var $entriesList VidiunMediaListResponse */

if(!$entriesList || !$entriesList->totalCount || !count($entriesList->objects))
{
	echo "No ready media entry found\n";
	exit(-1);
}
$entry = reset($entriesList->objects);
/* @var $entry VidiunMediaEntry */
echo "Found entry [$entry->id]\n";



/**
 * Calls stats.collect
 *
 * TODO:
 *  - Run it once on each API server.
 */
$client->getConfig()->method = VidiunClientBase::METHOD_GET;

$event = new VidiunStatsEvent();
$event->isFirstInSession = false;
$event->seek = false;

$event->clientVer = '3.0:v3.7';
$event->referrer =  $clientConfig->serviceUrl . 'sanity/tests';
$event->sessionId = uniqid('SANITY-TEST-');

$event->entryId = $entry->id;
$event->partnerId = $entry->partnerId;
$event->duration = $entry->duration;
$event->currentPoint = 0;

$event->uiconfId = $player->id;

$event->eventType = VidiunStatsEventType::WIDGET_LOADED;
$event->eventTimestamp = microtime(true);
try
{
	$client->stats->collect($event);
}
catch (VidiunClientException $e){}
echo "Sent event [VidiunStatsEventType::WIDGET_LOADED]\n";

$event->eventType = VidiunStatsEventType::MEDIA_LOADED;
$event->eventTimestamp = microtime(true);
try
{
	$client->stats->collect($event);
}
catch (VidiunClientException $e){}
echo "Sent event [VidiunStatsEventType::MEDIA_LOADED]\n";

$event->eventType = VidiunStatsEventType::PLAY;
$event->eventTimestamp = microtime(true);
try
{
	$client->stats->collect($event);
}
catch (VidiunClientException $e){}
echo "Sent event [VidiunStatsEventType::PLAY]\n";

$quarter = ceil(($entry->msDuration / 4) * 1000);

usleep($quarter);
$event->currentPoint += $quarter;
$event->eventType = VidiunStatsEventType::PLAY_REACHED_25;
$event->eventTimestamp = microtime(true);
try
{
	$client->stats->collect($event);
}
catch (VidiunClientException $e){}
echo "Sent event [VidiunStatsEventType::PLAY_REACHED_25]\n";

usleep($quarter);
$event->currentPoint += $quarter;
$event->eventType = VidiunStatsEventType::PLAY_REACHED_50;
$event->eventTimestamp = microtime(true);
try
{
	$client->stats->collect($event);
}
catch (VidiunClientException $e){}
echo "Sent event [VidiunStatsEventType::PLAY_REACHED_50]\n";

usleep($quarter);
$event->currentPoint += $quarter;
$event->eventType = VidiunStatsEventType::PLAY_REACHED_75;
$event->eventTimestamp = microtime(true);
try
{
	$client->stats->collect($event);
}
catch (VidiunClientException $e){}
echo "Sent event [VidiunStatsEventType::PLAY_REACHED_75]\n";

usleep($quarter);
$event->currentPoint = $entry->msDuration;
$event->eventType = VidiunStatsEventType::PLAY_REACHED_100;
$event->eventTimestamp = microtime(true);
try
{
	$client->stats->collect($event);
}
catch (VidiunClientException $e){}
echo "Sent event [VidiunStatsEventType::PLAY_REACHED_100]\n";

$client->getConfig()->method = VidiunClientBase::METHOD_POST;




/**
 * Rotate logs.
 */
$returnedValue = null;
$cmd = "$logrotate -f -vv $appDir/configurations/logrotate/vidiun_apache";
echo "Executing [$cmd]";
passthru($cmd, $returnedValue);
if($returnedValue !== 0)
{
	echo " failed\n";
	exit(-1);
}
echo " log rotated\n";



/**
 * Run hourly scripts.
 */
$cmd = "su $vidiunUser -c '$dwhDir/etlsource/execute/etl_hourly.sh -p $dwhDir  -k $kitchen_script '";
echo "Executing [$cmd]";
passthru($cmd, $returnedValue);
if($returnedValue !== 0)
{
	echo " failed\n";
	exit(-1);
}
echo " OK\n";


/**
 * Run update dimensions.
 */
$cmd = "su $vidiunUser -c '$dwhDir/etlsource/execute/etl_update_dims.sh -p $dwhDir -k $kitchen_script'";
echo "Executing [$cmd]";
passthru($cmd, $returnedValue);
//if($returnedValue !== 0)
//{
//	echo " failed [$cmd]\n";
//	exit(-1);
//}
echo " OK\n";



/**
 * Run daily scripts.
 */
$cmd = "su $vidiunUser -c '$dwhDir/etlsource/execute/etl_daily.sh -p $dwhDir -k $kitchen_script'";
echo "Executing [$cmd]";
passthru($cmd, $returnedValue);
if($returnedValue !== 0)
{
	echo " failed [$cmd]\n";
	exit(-1);
}
echo " OK\n";



/**
 * Validate the results using the API
 *
 * TODO:
 *  - Validate that the data collected from all API machines.
 */
$dateTimeZoneServer = new DateTimeZone(date_default_timezone_get());
$dateTimeZoneUTC = new DateTimeZone("UTC");
$dateTimeUTC = new DateTime("now", $dateTimeZoneUTC);
$timeOffsetSeconds = $dateTimeZoneServer->getOffset($dateTimeUTC);

$reportInputFilter = new VidiunReportInputFilter();
$reportInputFilter->timeZoneOffset = ($timeOffsetSeconds / -60);
$reportInputFilter->fromDay = date('Ymd', time() - (60 * 60 * 24));
$reportInputFilter->toDay = date('Ymd');

$reportInputPager = new VidiunFilterPager();
$reportTable = $client->report->getTable(VidiunReportType::TOP_CONTENT, $reportInputFilter, $reportInputPager, null, $entry->id);
/* @var $reportTable VidiunReportTable */

if($reportTable->totalCount != 1)
{
	echo "Reported wrong total count [$reportTable->totalCount]\n";
	exit(-1);
}

$titles = explode(',', $reportTable->header);
$data = explode(';', $reportTable->data);
if(!$reportTable->data || count($data) != 2)
{
	echo "Reported wrong data count\n";
	exit(-1);
}

$recordData = explode(',', reset($data));
$record = array_combine($titles, $recordData);
if($record['object_id'] != $entry->id)
{
	echo "Reported data of wrong entry [" . $record['object_id'] . "]\n";
	exit(-1);
}
if(!isset($record['count_plays']) || !$record['count_plays'] || !intval($record['count_plays']))
{
	echo "Reported wrong plays count [" . $record['count_plays'] . "]\n";
	exit(-1);
}
if(!isset($record['count_loads']) || !$record['count_loads'] || !intval($record['count_loads']))
{
	echo "Reported wrong loads count [" . $record['count_loads'] . "]\n";
	exit(-1);
}
$expectedTimeViewed = round($entry->duration / 60, 2);
if(!isset($record['sum_time_viewed']) || !$record['sum_time_viewed'] || floatval($record['sum_time_viewed']) < $expectedTimeViewed)
{
	echo "Reported wrong view time [" . $record['sum_time_viewed'] . "] expected at least [$expectedTimeViewed]\n";
	exit(-1);
}
echo "Reports OK\n";



/**
 * Syncyng plays and view from the dwh to the operational db
 */
$cmd = "su $vidiunUser -c $appDir/alpha/scripts/dwh/dwh_plays_views_sync.sh";
echo "Executing [$cmd]";
passthru($cmd, $returnedValue);
if($returnedValue !== 0)
{
	echo " failed [$cmd]\n";
	exit(-1);
}
echo " OK\n";



/**
 * Reload the entry and check plays and views
 */
$reloadedEntry = $client->media->get($entry->id);
/* @var $reloadedEntry VidiunMediaEntry */

if($reloadedEntry->plays <= $entry->plays)
{
	echo "Entry [$entry->id] plays [$reloadedEntry->plays] was not incremented\n";
	exit(-1);
}
if($reloadedEntry->views <= $entry->views)
{
	echo "Entry [$entry->id] views [$reloadedEntry->views] was not incremented\n";
	exit(-1);
}
echo "Plays and views OK\n";



echo "OK\n";
exit(0);

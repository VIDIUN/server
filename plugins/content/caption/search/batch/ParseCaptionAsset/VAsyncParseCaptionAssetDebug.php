<?php

/**
 * @package plugins.captionSearch
 * @subpackage Scheduler.Debug
 */

// /opt/vidiun/app/batch
chdir(dirname( __FILE__ ) . "/../../../../../../batch");

require_once(__DIR__ . "/../../../../../../batch/bootstrap.php");

$iniFile = "batch_config.ini";		// should be the full file path

$vdebuger = new VGenericDebuger($iniFile);
$vdebuger->run('VAsyncParseCaptionAsset');

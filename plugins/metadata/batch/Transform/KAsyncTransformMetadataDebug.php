<?php

/**
 * @package plugins.metadata
 * @subpackage Scheduler.Transform.Debug
 */

// /opt/vidiun/app/batch
chdir(dirname( __FILE__ ) . "/../../../../batch");

require_once(__DIR__ . "/../../../../batch/bootstrap.php");

$iniFile = "../configurations/batch";		// should be the full file path

$vdebuger = new VGenericDebuger($iniFile);
$vdebuger->run('VAsyncTransformMetadata');

?>
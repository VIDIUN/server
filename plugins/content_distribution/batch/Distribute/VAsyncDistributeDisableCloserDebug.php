<?php

/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute.Debug
 */

chdir(dirname( __FILE__ ) . "/../../../../batch");

require_once(__DIR__ . "/../../../../batch/bootstrap.php");

$iniFile = "batch_config.ini";		// should be the full file path

$vdebuger = new VGenericDebuger($iniFile);
$vdebuger->run('VAsyncDistributeDisableCloser');

<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */

chdir(dirname( __FILE__ ) . "/../../");

require_once(__DIR__ . "/../../../../../batch/bootstrap.php");

$iniFile = "batch_config.ini";		// should be the full file path

$vdebuger = new VGenericDebuger($iniFile);
$vdebuger->run('VAsyncTagResolve');
<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */

chdir(dirname( __FILE__ ) . "/../../");

require_once(__DIR__ . "/../../bootstrap.php");

$iniDir = "batch_config.ini";		// should be the full file path

$vdebuger = new VGenericDebuger($iniDir);
$vdebuger->run('VAsyncDbCleanup');

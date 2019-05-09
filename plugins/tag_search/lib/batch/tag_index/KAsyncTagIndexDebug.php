<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */

chdir(dirname( __FILE__ ) . "/../../../../../batch");

require_once(__DIR__ . "/../../../../../batch/bootstrap.php");

$iniFile = "../configurations/batch";;		// should be the full file path

$vdebuger = new VGenericDebuger($iniFile);
$vdebuger->run('VAsyncTagIndex');
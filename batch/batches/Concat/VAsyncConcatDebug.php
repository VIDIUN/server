<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */

chdir(dirname( __FILE__ ) . "/../../");

require_once(__DIR__ . "/../../bootstrap.php");

$iniDir = dirname ( __FILE__ ) . "/../configurations/batch";		// should be the full file path

$vdebuger = new VGenericDebuger($iniDir);
$vdebuger->run('VAsyncConcat');


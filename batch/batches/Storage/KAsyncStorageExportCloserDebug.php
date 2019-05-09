<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */

chdir(dirname( __FILE__ ) . "/../../");

require_once(__DIR__ . "/../../bootstrap.php");

$iniDir = dirname ( __FILE__ ) . "/../configurations/batch";
 
$vdebuger = new VGenericDebuger($iniDir);
$vdebuger->run('VAsyncStorageExportCloser');

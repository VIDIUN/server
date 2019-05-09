<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncVirusScan
 * 
 * @package plugins.virusScan
 * @subpackage Scheduler
 */

$instance = new VAsyncVirusScan();
$instance->run(); 
$instance->done();
?>
<?php
/**
 * Will run the VAsyncDbCleanup 
 *
 * @package Scheduler
 * @subpackage Cleanup
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncServerNodeMonitor();
$instance->run(); 
$instance->done();

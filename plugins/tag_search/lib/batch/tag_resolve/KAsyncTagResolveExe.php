<?php
/**
 * Will run the VAsyncDirectoryCleanup 
 *
 * @package Scheduler
 * @subpackage Cleanup
 */
require_once(__DIR__ . "/../../../../../batch/bootstrap.php");

$instance = new VAsyncTagResolve ();
$instance->run(); 
$instance->done();
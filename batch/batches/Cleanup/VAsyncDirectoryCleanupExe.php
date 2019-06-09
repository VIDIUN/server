<?php
/**
 * Will run the VAsyncDirectoryCleanup 
 *
 * @package Scheduler
 * @subpackage Cleanup
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncDirectoryCleanup ( );
$instance->run(); 
$instance->done();

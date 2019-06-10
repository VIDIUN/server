<?php
/**
 * Will run the VAsyncBalancer 
 *
 * @package Scheduler
 * @subpackage Cleanup
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncJobSuspender();
$instance->run(); 
$instance->done();

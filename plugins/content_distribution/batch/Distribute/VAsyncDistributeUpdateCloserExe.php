<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncDistributeUpdateCloser
 * 
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */

$instance = new VAsyncDistributeUpdateCloser();
$instance->run(); 
$instance->done();

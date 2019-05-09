<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncDistributeFetchReportCloser
 * 
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */

$instance = new VAsyncDistributeFetchReportCloser();
$instance->run(); 
$instance->done();

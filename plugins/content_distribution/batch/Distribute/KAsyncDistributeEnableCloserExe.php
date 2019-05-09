<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncDistributeEnableCloser
 * 
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */

$instance = new VAsyncDistributeEnableCloser();
$instance->run(); 
$instance->done();

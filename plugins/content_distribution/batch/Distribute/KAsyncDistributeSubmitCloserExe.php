<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncDistributeSubmitCloser
 * 
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */

$instance = new VAsyncDistributeSubmitCloser();
$instance->run(); 
$instance->done();

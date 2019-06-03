<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncDistributeUpdate
 * 
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */

$instance = new VAsyncDistributeUpdate();
$instance->run(); 
$instance->done();

<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncIntegrate
 * 
 * @package plugins.integration
 * @subpackage Scheduler
 */

$instance = new VAsyncIntegrate();
$instance->run(); 
$instance->done();

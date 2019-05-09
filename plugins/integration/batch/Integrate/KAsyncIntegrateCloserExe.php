<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncIntegrateCloser
 * 
 * @package plugins.integration
 * @subpackage Scheduler
 */

$instance = new VAsyncIntegrateCloser();
$instance->run(); 
$instance->done();

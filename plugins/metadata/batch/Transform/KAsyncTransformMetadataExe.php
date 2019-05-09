<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncTransformMetadata
 * 
 * @package plugins.metadata
 * @subpackage Scheduler.Transform
 */

$instance = new VAsyncTransformMetadata();
$instance->run(); 
$instance->done();
?>
<?php

require_once(__DIR__ . "/../../../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncWidevineRepositorySync
 * 
 * @package plugins.widevine
 * @subpackage Scheduler
 */

$instance = new VAsyncWidevineRepositorySync();
$instance->run(); 
$instance->done();

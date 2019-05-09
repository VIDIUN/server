<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Will run the VAsyncFileSyncImport
 *
 * @package plugins.multiCenters
 * @subpackage Scheduler.FileSyncImport
 */

$instance = new VAsyncFileSyncImport();
$instance->run();
$instance->done();
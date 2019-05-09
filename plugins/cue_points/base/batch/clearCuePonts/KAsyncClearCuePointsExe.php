<?php
/**
 * Will run the VAsyncClearCuePoints 
 *
 * @package Scheduler
 * @subpackage ClearCuePoints
 */
require_once(__DIR__ . "/../../../../../batch/bootstrap.php");

$instance = new VAsyncClearCuePoints();
$instance->run();
$instance->done();

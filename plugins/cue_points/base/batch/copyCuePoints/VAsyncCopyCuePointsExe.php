<?php
/**
 * Will run the VAsyncCopyCuePoints
 *
 * @package Scheduler
 * @subpackage copyCuePoints
 */
require_once(__DIR__ . "/../../../../../batch/bootstrap.php");

$instance = new VAsyncCopyCuePoints();
$instance->run();
$instance->done();

<?php

/**
 * Executes the VAsyncCopyCaptions
 *
 * @package plugins.caption
 * @subpackage Scheduler
 */
require_once(__DIR__ . "/../../../../../../batch/bootstrap.php");

$instance = new VAsyncCopyCaptions();
$instance->run();
$instance->done();

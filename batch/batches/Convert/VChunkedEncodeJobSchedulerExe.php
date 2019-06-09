<?php
/**
 * Will run VChunkedEncodeJobScheduler
 *
 * @package Scheduler
 * @subpackage ChunkedEncode
 */
//require_once(__DIR__ . "/../../bootstrap.php");
require_once("/opt/vidiun/app/batch/bootstrap.php");

$instance = new VChunkedEncodeJobScheduler();
$instance->run();
$instance->done();


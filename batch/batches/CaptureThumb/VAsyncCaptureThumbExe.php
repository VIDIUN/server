<?php
/**
 * Will run VAsyncCaptureThumb
 *
 * 
 * @package Scheduler
 * @subpackage Capture-Thumbnail
 */
chdir(dirname(__FILE__) . '/../../');
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncCaptureThumb();
$instance->run(); 
$instance->done();

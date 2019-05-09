<?php
/**
 * Will run VAsyncConvertLiveSegment.class.php 
 * 
 *
 * @package Scheduler
 * @subpackage Conversion
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncConvertLiveSegment();
$instance->run(); 
$instance->done();
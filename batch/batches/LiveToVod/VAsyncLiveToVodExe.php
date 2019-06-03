<?php
/**
 * Executes the VAsyncCopyCuePointFromLiveToVod
 * 
 * @package Scheduler
 * @subpackage Copy
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncLiveToVod();
$instance->run(); 
$instance->done();

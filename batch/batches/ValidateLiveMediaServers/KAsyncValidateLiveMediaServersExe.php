<?php
/**
 * Will run the VAsyncValidateLiveMediaServers 
 *
 * @package Scheduler
 * @subpackage ValidateLiveMediaServers
 */
require_once (__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncValidateLiveMediaServers();
$instance->run();
$instance->done();

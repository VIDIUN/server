<?php
require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncDispatchEventNotification
 * 
 * @package plugins.eventNotification
 * @subpackage Scheduler
 */

$instance = new VAsyncDispatchEventNotification();
$instance->run(); 
$instance->done();

<?php
/**
 * Will run VScheduleHelper 
 *
 * @package Scheduler
 */
require_once(__DIR__ . "/../bootstrap.php");

$instance = new VScheduleHelper();
$instance->run(); 
$instance->done();

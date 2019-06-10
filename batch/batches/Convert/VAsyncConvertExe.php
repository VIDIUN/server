<?php
/**
 * Will run VAsyncConvert
 *
 * @package Scheduler
 * @subpackage Conversion
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncConvert();
$instance->run(); 
$instance->done();

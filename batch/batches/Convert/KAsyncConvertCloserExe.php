<?php
/**
 * Will run VAsyncConvertCloser
 *
 * @package Scheduler
 * @subpackage Conversion
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncConvertCloser();
$instance->run(); 
$instance->done();

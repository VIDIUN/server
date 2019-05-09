<?php
/**
 * Will run VAsyncConvertProfileCloser
 *
 * @package Scheduler
 * @subpackage Conversion
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncConvertProfileCloser();
$instance->run(); 
$instance->done();

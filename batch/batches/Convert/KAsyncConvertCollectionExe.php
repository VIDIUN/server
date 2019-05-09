<?php
/**
 * Will run VAsyncConvertCollection
 *
 * @package Scheduler
 * @subpackage Conversion
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncConvertCollection();
$instance->run(); 
$instance->done();

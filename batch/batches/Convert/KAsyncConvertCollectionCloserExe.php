<?php
/**
 * Will run VAsyncConvertCollectionCloser
 *
 * @package Scheduler
 * @subpackage Conversion
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncConvertCollectionCloser();
$instance->run(); 
$instance->done();

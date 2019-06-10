<?php
/**
 * Will run the VAsyncEmailIngestion
 *
 * @package Scheduler
 * @subpackage Email-Ingestion
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncEmailIngestion();
$instance->run();
$instance->done();

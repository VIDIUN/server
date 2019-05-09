<?php
/**
 * Will run VAsyncBulkDownloadCloser
 *
 * @package Scheduler
 * @subpackage Bulk-Download
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncBulkDownloadCloser();
$instance->run(); 
$instance->done();

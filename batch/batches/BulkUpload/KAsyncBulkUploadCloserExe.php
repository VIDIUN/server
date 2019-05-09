<?php
/**
 * Will run VAsyncBulkUploadCloser
 *
 * @package Scheduler
 * @subpackage Bulk-Upload
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncBulkUploadCloser();
$instance->run(); 
$instance->done();

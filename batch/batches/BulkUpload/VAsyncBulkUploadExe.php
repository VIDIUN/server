<?php
/**
 * Will run VAsyncBulkUpload
 *
 * @package Scheduler
 * @subpackage Bulk-Upload
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncBulkUpload();
$instance->run(); 
$instance->done();

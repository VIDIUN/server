<?php
/**
 * Will run VAsyncLiveReportExport
 *
 * @package Scheduler
 * @subpackage LiveReportExport
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncLiveReportExport();
$instance->run(); 
$instance->done();

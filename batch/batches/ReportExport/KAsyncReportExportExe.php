<?php
/**
 * Will run VAsyncReportExport
 *
 * @package Scheduler
 * @subpackage ReportExport
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncReportExport();
$instance->run();
$instance->done();
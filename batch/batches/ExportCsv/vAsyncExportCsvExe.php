<?php
/**
 * Executes the VAsyncUsersCsv
 *
 * @package Scheduler
 * @subpackage Users-Csv
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncExportCsv();
$instance->run();
$instance->done();
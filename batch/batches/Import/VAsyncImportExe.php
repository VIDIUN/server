<?php
/**
 * Executes the VAsyncImport
 * 
 * @package Scheduler
 * @subpackage Import
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncImport();
$instance->run(); 
$instance->done();

<?php
/**
 * Executes the VAsyncMoveCategoryEntries
 * 
 * @package Scheduler
 * @subpackage MoveCategoryEntries
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncMoveCategoryEntries();
$instance->run(); 
$instance->done();

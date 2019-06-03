<?php
/**
 * Executes the VAsyncRecalculateCache
 * 
 * @package Scheduler
 * @subpackage RecalculateCache
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncRecalculateCache();
$instance->run(); 
$instance->done();

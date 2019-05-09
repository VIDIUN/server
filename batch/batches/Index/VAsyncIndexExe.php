<?php
/**
 * Executes the VAsyncIndex
 * 
 * @package Scheduler
 * @subpackage Index
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncIndex();
$instance->run(); 
$instance->done();

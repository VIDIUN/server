<?php
/**
 * Executes the VAsyncCopy
 * 
 * @package Scheduler
 * @subpackage Copy
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncCopy();
$instance->run(); 
$instance->done();

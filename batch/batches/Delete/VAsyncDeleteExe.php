<?php
/**
 * Executes the VAsyncDelete
 * 
 * @package Scheduler
 * @subpackage Delete
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncDelete();
$instance->run(); 
$instance->done();

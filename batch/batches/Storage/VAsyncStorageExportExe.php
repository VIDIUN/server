<?php
/**
 * Will run VAsyncStorageExport.class.php 
 * 
 *
 * @package Scheduler
 * @subpackage Storage
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncStorageExport();
$instance->run(); 
$instance->done();

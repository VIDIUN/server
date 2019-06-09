<?php
/**
 * Will run VAsyncStorageDelete.class.php 
 * 
 *
 * @package Scheduler
 * @subpackage Storage
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncDeleteFile();
$instance->run(); 
$instance->done();
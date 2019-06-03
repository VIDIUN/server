<?php
/**
 * Will run VAsyncStorageDelete.class.php 
 * 
 *
 * @package Scheduler
 * @subpackage Storage
 */

chdir(dirname( __FILE__ ) . "/../../");
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncStorageExportCloser();
$instance->run(); 
$instance->done();

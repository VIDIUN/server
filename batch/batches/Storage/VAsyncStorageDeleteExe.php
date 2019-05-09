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

$instance = new VAsyncStorageDelete();
$instance->run(); 
$instance->done();

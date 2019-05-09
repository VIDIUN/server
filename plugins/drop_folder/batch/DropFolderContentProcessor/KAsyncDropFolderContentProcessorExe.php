<?php

require_once(__DIR__ . "/../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncDropFolderContentProcessor
 * 
 * @package plugins.dropFolder
 * @subpackage Scheduler
 */

$instance = new VAsyncDropFolderContentProcessor();
$instance->run(); 
$instance->done();

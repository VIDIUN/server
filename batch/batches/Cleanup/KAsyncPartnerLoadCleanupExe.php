<?php
/**
 * Will run the VAsyncPartnerLoadCleanup 
 *
 * @package Scheduler
 * @subpackage Cleanup
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncPartnerLoadCleanup ( );
$instance->run(); 
$instance->done();

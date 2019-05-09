<?php
/**
 * Will run VAsyncNotifier 
 * 
 * 
 * @package Scheduler
 * @subpackage Notifier
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncNotifier ( );
$instance->run(); 
$instance->done();

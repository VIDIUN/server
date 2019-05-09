<?php
/**
 * Will run VAsyncProvisionDelete
 *
 * @package Scheduler
 * @subpackage Provision
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncProvisionDelete();
$instance->run(); 
$instance->done();

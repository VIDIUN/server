<?php
/**
 * Will run VAsyncProvisionProvide
 *
 * @package Scheduler
 * @subpackage Provision
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncProvisionProvide();
$instance->run(); 
$instance->done();

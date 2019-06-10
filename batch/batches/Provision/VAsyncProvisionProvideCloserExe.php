<?php
/**
 * Will run VAsyncProvisionProvide
 *
 * @package Scheduler
 * @subpackage Provision
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncProvisionProvideCloser();
$instance->run(); 
$instance->done();

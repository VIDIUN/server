<?php
/**
 * Executes the VAsyncCopyPartner
 * 
 * @package Scheduler
 * @subpackage CopyPartner
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncCopyPartner();
$instance->run(); 
$instance->done();

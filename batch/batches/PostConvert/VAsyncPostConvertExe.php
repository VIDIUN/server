<?php
/**
 * Will run VAsyncPostConvert
 *
 * 
 * @package Scheduler
 * @subpackage Post-Convert
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncPostConvert();
$instance->run(); 
$instance->done();

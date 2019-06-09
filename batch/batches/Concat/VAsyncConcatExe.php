<?php
/**
 * Will run VAsyncConcat.class.php 
 * 
 *
 * @package Scheduler
 * @subpackage Conversion
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncConcat();
$instance->run(); 
$instance->done();
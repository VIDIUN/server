<?php
/**
 * Will run VAsyncExtractMedia.class.php 
 * 
 *
 * @package Scheduler
 * @subpackage Extract-Media
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncExtractMedia();
$instance->run(); 
$instance->done();

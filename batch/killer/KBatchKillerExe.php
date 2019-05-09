<?php
/**
 * Will run VBatchKiller
 * 
 * @package Scheduler
 * @subpackage Monitor
 */
require_once(__DIR__ . "/../bootstrap.php");

$config = unserialize(base64_decode($argv[1]));

$instance = new VBatchKiller($config);
$instance->run(); 

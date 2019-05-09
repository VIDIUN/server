<?php
/**
 * Executes the VAsyncMailer
 *
 * @package Scheduler
 * @subpackage Mailer
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncMailer();
$instance->run(); 
$instance->done();

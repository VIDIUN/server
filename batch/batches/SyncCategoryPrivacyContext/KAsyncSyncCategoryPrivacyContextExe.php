<?php
/**
 * Executes the VAsyncSyncCategoryPrivacyContext
 * 
 * @package Scheduler
 * @subpackage SyncCategoryPrivacyContext
 */
require_once(__DIR__ . "/../../bootstrap.php");

$instance = new VAsyncSyncCategoryPrivacyContext();
$instance->run(); 
$instance->done();

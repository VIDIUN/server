<?php
require_once(__DIR__ . "/../../../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncParseCaptionAsset
 * 
 * @package plugins.captionSearch
 * @subpackage Scheduler
 */

$instance = new VAsyncParseCaptionAsset();
$instance->run(); 
$instance->done();

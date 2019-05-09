<?php
require_once(__DIR__ . "/../../../../../../batch/bootstrap.php");

/**
 * Executes the VAsyncParseMultiLanguageCaptionAsset
 * 
 * @package plugins.caption
 * @subpackage Scheduler
 */

$instance = new VAsyncParseMultiLanguageCaptionAsset();
$instance->run(); 
$instance->done();

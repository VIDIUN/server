<?php
/**
 * @package plugins.integration
 * @subpackage Scheduler.Debug
 */
chdir(dirname(__FILE__) . "/../../../../batch");

require_once(__DIR__ . "/../../../../batch/bootstrap.php");

$iniFile = realpath(dirname(__FILE__) . "/../../../../configurations/batch");

$vdebuger = new VGenericDebuger($iniFile);
$vdebuger->run('VAsyncIntegrateCloser');

<?php
/**
 * @package plugins.eventNotification
 * @subpackage Scheduler.Debug
 */

// /opt/vidiun/app/batch
chdir(dirname(__FILE__) . "/../../../../batch");

require_once(__DIR__ . "/../../../../batch/bootstrap.php");

$iniFile = realpath(dirname(__FILE__) . "/../../../../configurations/batch");

$vdebuger = new VGenericDebuger($iniFile);
$vdebuger->run('VAsyncDispatchEventNotification');

<?php
/**
 *
 * @package Scheduler
 */

if(strtoupper(PHP_SAPI) != 'CLI' && strtoupper(PHP_SAPI) != 'CGI-FCGI')
{
	echo 'This script must be executed using CLI.';
	exit (1);
}

$phpPath = 'php';
if(isset($argc) && $argc > 1)
{
	$phpPath = $argv[1];
}
else if(isset($_SERVER['PHP_PEAR_PHP_BIN']))
{
	$phpPath = $_SERVER['PHP_PEAR_PHP_BIN'];
}

$iniDir = dirname ( __FILE__ ) . "/../configurations/batch";		// should be the full file path

if(isset($argc) && $argc > 2)
{
	$iniDir = $argv[2];
}

if(!file_exists($iniDir))
{
	die("Configuration file [$iniDir] not found.");
}

require_once(__DIR__ . "/bootstrap_scheduler.php");

$vscheduler = new VGenericScheduler($phpPath, $iniDir);
$vscheduler->run();

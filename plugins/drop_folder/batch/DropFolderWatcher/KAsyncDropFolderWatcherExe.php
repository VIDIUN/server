<?php

ini_set ( "memory_limit", "256M" );

require_once(__DIR__ . "/../../../../batch/bootstrap.php");

//will free the exclusive drop folder when getting kill SIGNAL
function gracefullyKill()
{
	global $instance; //as the VAsyncDropFolderWatcher
	$instance->preKill();
	exit();
}
declare(ticks = 1);
pcntl_signal(SIGINT, 'gracefullyKill');
pcntl_signal(SIGTERM, 'gracefullyKill');

/**
 * Executes the VAsyncDropFolderWatcher
 * 
 * @package plugins.dropFolder
 * @subpackage Scheduler
 */

$instance = new VAsyncDropFolderWatcher();
$instance->run(); 
$instance->done();

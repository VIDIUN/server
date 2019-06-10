<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */
chdir(dirname( __FILE__ ) . "/../../");
require_once(__DIR__ . "/../../bootstrap.php");

$iniDir = "../configurations/batch";		// should be the full file path

try
{
	$vdebuger = new VGenericDebuger($iniDir);
	$vdebuger->run('VAsyncCopyPartner');
}
catch ( Exception $e )
{
	echo $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString(); 
}
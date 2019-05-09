<?php
/**
 * 
 * @package Scheduler
 */

chdir(__DIR__);
define('VIDIUN_ROOT_PATH', realpath(__DIR__ . '/../'));
require_once(VIDIUN_ROOT_PATH . '/alpha/config/vConf.php');

define("VIDIUN_BATCH_PATH", VIDIUN_ROOT_PATH . "/batch");

// Autoloader - override the autoloader defaults
require_once(VIDIUN_ROOT_PATH . "/infra/VAutoloader.php");
VAutoloader::setClassPath(array(
	VAutoloader::buildPath(VIDIUN_ROOT_PATH, "infra", "*"),
	VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "*"),
	VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*"),
	VAutoloader::buildPath(VIDIUN_BATCH_PATH, "*"),
));

VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*", "batch", "*"));

VAutoloader::setIncludePath(array(
	VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "ZendFramework", "library"),
));
VAutoloader::setClassMapFilePath(vEnvironment::get("cache_root_path") . '/batch/classMap.cache');
VAutoloader::register();

// Logger
$loggerConfigPath = VIDIUN_ROOT_PATH . "/configurations/logger.ini";

try // we don't want to fail when logger is not configured right
{
	$config = new Zend_Config_Ini($loggerConfigPath);
	VidiunLog::initLog($config->batch_scheduler);
	VidiunLog::setContext("BATCH");
}
catch(Zend_Config_Exception $ex)
{
}


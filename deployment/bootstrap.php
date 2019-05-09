<?php

set_time_limit(0);
ini_set("memory_limit","2048M");

define('ROOT_DIR', realpath(dirname(__FILE__) . '/../'));
require_once(ROOT_DIR . '/alpha/config/vConf.php');
require_once(ROOT_DIR . '/infra/VAutoloader.php');

VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "propel", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*"));
VAutoloader::setClassMapFilePath(vConf::get("cache_root_path") . '/deploy/classMap.cache');
VAutoloader::register();

date_default_timezone_set(vConf::get("date_default_timezone")); // America/New_York

$loggerConfigPath = realpath(VIDIUN_ROOT_PATH . DIRECTORY_SEPARATOR . "configurations" . DIRECTORY_SEPARATOR . "logger.ini");

try // we don't want to fail when logger is not configured right
{
	$config = new Zend_Config_Ini($loggerConfigPath);
	$deploy = $config->deploy;
	
	VidiunLog::initLog($deploy);
}
catch(Zend_Config_Exception $ex)
{
}

DbManager::setConfig(vConf::getDB());
DbManager::initialize();
<?php
set_time_limit(0);

ini_set("memory_limit","700M");

define("VIDIUN_ROOT_PATH", realpath(__DIR__ . '/../../'));
require_once(VIDIUN_ROOT_PATH . '/alpha/config/vConf.php');
require_once(VIDIUN_ROOT_PATH . '/infra/VAutoloader.php');

VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "infra", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*"));
VAutoloader::setClassMapFilePath(vConf::get("cache_root_path") . '/scripts/classMap.cache');
VAutoloader::addExcludePath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "aws", "*")); // Do not load AWS files
VAutoloader::addExcludePath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "HTMLPurifier", "*")); // Do not load HTMLPurifier files
VAutoloader::register();

date_default_timezone_set(vConf::get("date_default_timezone"));

$loggerConfigPath = VIDIUN_ROOT_PATH.'/configurations/logger.ini';
try
{
	$config = new Zend_Config_Ini($loggerConfigPath);
	VidiunLog::initLog($config->scripts);
	VidiunLog::setContext(basename($_SERVER['SCRIPT_NAME']));
}
catch (Zend_Config_Exception $ex)
{
	
}
VidiunLog::info("Starting script");

VidiunLog::info("Initializing database...");
DbManager::setConfig(vConf::getDB());
DbManager::initialize();
VidiunLog::info("Database initialized successfully");

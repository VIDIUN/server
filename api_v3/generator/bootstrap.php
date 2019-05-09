<?php

if (!defined("VIDIUN_ROOT_PATH"))			// may already be defined when invoked through vwidgetAction
	define("VIDIUN_ROOT_PATH", realpath(__DIR__ . '/../../'));
if (!defined("SF_ROOT_DIR"))				// may already be defined when invoked through vwidgetAction
	define('SF_ROOT_DIR', VIDIUN_ROOT_PATH . '/alpha');
define("VIDIUN_API_V3", true); // used for different logic in alpha libs

define("VIDIUN_API_PATH", VIDIUN_ROOT_PATH.DIRECTORY_SEPARATOR."api_v3");
require_once(VIDIUN_API_PATH.DIRECTORY_SEPARATOR.'VERSION.php'); //defines VIDIUN_API_VERSION
require_once (VIDIUN_ROOT_PATH.DIRECTORY_SEPARATOR.'alpha'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'vConf.php');


// Autoloader
require_once(VIDIUN_ROOT_PATH.DIRECTORY_SEPARATOR."infra".DIRECTORY_SEPARATOR."VAutoloader.php");
VAutoloader::setClassMapFilePath(vConf::get("cache_root_path") . '/api_v3/classMap.cache');
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "propel", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "nusoap", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_API_PATH, "lib", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_API_PATH, "services", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "alpha", "plugins", "*")); // needed for testmeDoc
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*"));
VAutoloader::register();


// Timezone
date_default_timezone_set(vConf::get("date_default_timezone")); // America/New_York

// Logger
vLoggerCache::InitLogger('generator');
VidiunLog::setContext("API");

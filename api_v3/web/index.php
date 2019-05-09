<?php

header('Access-Control-Expose-Headers: Server, Content-Length, Content-Range, Date, X-Vidiun, X-Vidiun-Session, X-Me');

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Range, Cache-Control');
	header('Access-Control-Allow-Methods: POST, GET, HEAD, OPTIONS');
	header('Access-Control-Max-Age: 86400');
	exit;
}

$start = microtime(true);
// check cache before loading anything
require_once(dirname(__FILE__)."/../lib/VidiunResponseCacher.php");
$cache = new VidiunResponseCacher();
$cache->checkOrStart();

require_once(dirname(__FILE__)."/../bootstrap.php");

// Database
DbManager::setConfig(vConf::getDB());
DbManager::initialize();

ActKeyUtils::checkCurrent();

VidiunLog::debug(">------------------------------------- api_v3 -------------------------------------");
VidiunLog::info("API-start pid:".getmypid());

$controller = VidiunFrontController::getInstance();
$result = $controller->run();

$end = microtime(true);
VidiunLog::info("API-end [".($end - $start)."]");
VidiunLog::debug("<------------------------------------- api_v3 -------------------------------------");

$cache->end($result);

<?php

define('VIDIUN_ROOT_PATH',				realpath(__DIR__ . '/../'));

define('SF_APP',						'vidiun');
define('SF_ROOT_DIR',					VIDIUN_ROOT_PATH . '/alpha');
define('MODULES', 						SF_ROOT_DIR . '/apps/vidiun/modules/');


$sf_symfony_lib_dir = VIDIUN_ROOT_PATH . '/vendor/symfony';
$sf_symfony_data_dir = VIDIUN_ROOT_PATH . '/vendor/symfony-data';

// symfony bootstraping
require_once("$sf_symfony_lib_dir/util/sfCore.class.php");
sfCore::bootstrap($sf_symfony_lib_dir, $sf_symfony_data_dir);

// Logger
vLoggerCache::InitLogger(VIDIUN_LOG, 'PS2');

sfLogger::getInstance()->registerLogger(VidiunLog::getInstance());
sfLogger::getInstance()->setLogLevel(7);
sfConfig::set('sf_logging_enabled', true);

DbManager::setConfig(vConf::getDB());
DbManager::initialize();

ActKeyUtils::checkCurrent();
sfContext::getInstance()->getController()->dispatch();

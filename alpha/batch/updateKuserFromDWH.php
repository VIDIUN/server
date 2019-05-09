<?php
error_reporting ( E_ALL );
set_time_limit(0);

ini_set("memory_limit","700M");

define("VIDIUN_ROOT_PATH", realpath(__DIR__ . '/../../'));

require_once(VIDIUN_ROOT_PATH . '/alpha/config/vConf.php');
require_once(VIDIUN_ROOT_PATH . '/infra/VAutoloader.php');

$sf_symfony_lib_dir = realpath(dirname(__FILE__).'/../../vendor/symfony');
$sf_symfony_data_dir = realpath(dirname(__FILE__).'/../../vendor/symfony-data');

$include_path = realpath(dirname(__FILE__).'/../../vendor/ZendFramework/library') . PATH_SEPARATOR . get_include_path();
set_include_path($include_path);

require_once($sf_symfony_lib_dir.'/util/sfCore.class.php');
sfCore::bootstrap($sf_symfony_lib_dir, $sf_symfony_data_dir);

VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "propel", "*"));
VAutoloader::setClassMapFilePath(vConf::get("cache_root_path") . '/scripts/' . basename(__FILE__) . '.cache');
VAutoloader::register();

date_default_timezone_set(vConf::get("date_default_timezone"));

$loggerConfigPath = VIDIUN_ROOT_PATH . '/scripts/logger.ini';
$config = new Zend_Config_Ini($loggerConfigPath);
VidiunLog::initLog($config);
VidiunLog::setContext(basename(__FILE__));
VidiunLog::info("Starting script");

VidiunLog::info("Initializing database...");
DbManager::setConfig(vConf::getDB());
DbManager::initialize();
VidiunLog::info("Database initialized successfully");

$syncType = 'vuser';
$dbh = myDbHelper::getConnection ( myDbHelper::DB_HELPER_CONN_DWH );
$sql = "CALL get_data_for_operational('$syncType')";
$count = 0;
$rows = $dbh->query ( $sql )->fetchAll ();
foreach ( $rows as $row ) {
	$vuser = vuserPeer::retrieveByPK ( $row ['vuser_id'] );
	if (is_null ( $vuser )) {
		VidiunLog::err ( 'Couldn\'t find vuser [' . $row ['vuser_id'] . ']' );
		continue;
	}
	$vuser->setStorageSize ( $row ['storage_size'] );
	$vuser->save ();
	$count ++;
	VidiunLog::debug ( 'Successfully saved vuser [' . $row ['vuser_id'] . ']' );
	if ($count % 500)
		vuserPeer::clearInstancePool ();
}
$sql = "CALL mark_operational_sync_as_done('$syncType')";
$dbh->query ( $sql );
VidiunLog::debug ( "Done updating $count vusers from DWH to operational DB" );
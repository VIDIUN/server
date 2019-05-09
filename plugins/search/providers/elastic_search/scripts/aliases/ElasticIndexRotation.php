<?php


if ($argc < 2)
{
	echo "Usage:\n\t" . basename(__file__) . " <config path>\n";
	die;
}

$configPath = $argv[1];
if (!file_exists($configPath))
{
	die("Config file [$configPath] doesn't exists\n");
}

chdir(dirname(__FILE__));
define('ROOT_DIR', realpath(dirname(__FILE__) . '/../../../../../../'));
require_once(ROOT_DIR . '/infra/VAutoloader.php');
require_once(ROOT_DIR . '/alpha/config/vConf.php');
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*"));
VAutoloader::setClassMapFilePath(vConf::get("cache_root_path") . '/elastic/' . basename(__FILE__) . '.cache');
VAutoloader::register();
error_reporting(E_ALL);
VidiunLog::setLogger(new VidiunStdoutLogger());

$dryRun = false;
$configSections = parse_ini_file($configPath, true);
foreach ($configSections as $configSection)
{
	$rotationWorker = new ElasticIndexRotationWorker($configSection, $dryRun);
	$rotationWorker->rotate();	
}

VidiunLog::log("Done!");

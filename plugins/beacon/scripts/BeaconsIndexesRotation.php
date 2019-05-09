<?php


if ($argc > 1 && in_array($argv[1], array('--help', '-help', '-h', '-?')))
{
	echo "Usage:\n\t" . basename(__file__) . " <dryRun> <handleUnusedIndices>\n";
	die;
}

if($argc > 1)
{
	$dryRun = $argv[1];
}
else
{
	$dryRun = false;
}

if($argc > 2)
{
	$handleUnusedIndices = $argv[2];
}
else
{
	$handleUnusedIndices = false;
}

chdir(dirname(__FILE__));
define('ROOT_DIR', realpath(dirname(__FILE__) . '/../../../'));
require_once(ROOT_DIR . '/infra/VAutoloader.php');
require_once(ROOT_DIR . '/alpha/config/vConf.php');
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*"));
VAutoloader::setClassMapFilePath(vConf::get("cache_root_path") . '/elastic/' . basename(__FILE__) . '.cache');
VAutoloader::register();
error_reporting(E_ALL);
VidiunLog::setLogger(new VidiunStdoutLogger());

$beaconElasticConfig = vConf::getMap('beacon_rotation');
foreach ($beaconElasticConfig as $configSection)
{
	$rotationWorker = new BeaconsIndexesRotationWorker($configSection, $dryRun, $handleUnusedIndices);
	$rotationWorker->rotate();
}

VidiunLog::log("Done!");

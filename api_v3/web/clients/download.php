<?php
require_once(__DIR__ . "/../../bootstrap.php");
VidiunLog::setContext("CLIENTS");
VidiunLog::debug(__FILE__ . " start");
$requestedName = isset($_GET["name"]) ? $_GET['name'] : null;
if (!$requestedName)
	die("File not found");

$generatorOutputPath = VAutoloader::buildPath(VIDIUN_ROOT_PATH, "generator", "output");
$generatorConfigPath = VAutoloader::buildPath(VIDIUN_ROOT_PATH, "generator", "config.ini");
$config = new Zend_Config_Ini($generatorConfigPath);
foreach($config as $name => $item)
{
	if ($name === $requestedName && $item->get("public-download"))
	{
		$fileName = $name.".tar.gz";
		$outputFilePath = VAutoloader::buildPath($generatorOutputPath, $fileName);
		$outputFilePath = realpath($outputFilePath);
		header("Content-disposition: attachment; filename=$fileName");
		vFileUtils::dumpFile($outputFilePath, "application/gzip");
		die;
	}
}
die("File not found");
VidiunLog::debug(__FILE__ . " end");
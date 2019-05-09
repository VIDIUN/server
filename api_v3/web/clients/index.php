<?php
require_once(__DIR__ . "/../../bootstrap.php");
VidiunLog::setContext("CLIENTS");
VidiunLog::debug(__FILE__ . " start");

$generatorPath 			= VAutoloader::buildPath(VIDIUN_ROOT_PATH, "generator");
$generatorOutputPath 	= VAutoloader::buildPath(VIDIUN_ROOT_PATH, "generator", "output");
$generatorConfigPath 	= VAutoloader::buildPath(VIDIUN_ROOT_PATH, "generator", "config.ini");
$config = new Zend_Config_Ini($generatorConfigPath);
?>
<ul>
<?php 
foreach($config as $name => $item)
{
	if (!$item->get("public-download"))
		continue;
		
	$outputFilePath = VAutoloader::buildPath($generatorOutputPath, $name.".tar.gz");
	$outputFileRealPath = realpath($outputFilePath);
	if ($outputFileRealPath)
	{
		print('<li>');
		print('<a href="download.php?name='.$name.'"> Download '.$name.'</a>');
		print('</li>');
	}
}
?>
</ul>
<?php 
VidiunLog::debug(__FILE__ . " end");
<?php

require_once(__DIR__ . "/../bootstrap.php");

$root = myContentStorage::getFSContentRootPath();
$outputPathBase = "$root/content/clientlibs";

$fileLocation = "$outputPathBase/VidiunClient.xml";

if (!file_exists($fileLocation))
	die("VidiunClient.xml was not found");
	
header("Content-Type: text/xml");
readfile($fileLocation);

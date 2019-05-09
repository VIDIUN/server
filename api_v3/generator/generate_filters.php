<?php 
error_reporting(E_ALL);
ini_set( "memory_limit","512M" );

chdir(__DIR__);

//bootstrap connects the generator to the rest of Vidiun system
require_once(__DIR__ . "/bootstrap.php");

VidiunLog::info("Generating API filters");
$xmlGenerator = new FiltersGenerator();
$xmlGenerator->generate();

VidiunLog::info("Filters generated");

<?php
// This file generated by Propel  convert-conf target
// from XML runtime conf file C:\opt\vidiun\app\alpha\config\runtime-conf.xml
return array (
  'datasources' => 
  array (
    'vidiun' => 
    array (
      'adapter' => 'mysql',
      'connection' => 
      array (
        'phptype' => 'mysql',
        'database' => 'vidiun',
        'hostspec' => 'localhost',
        'username' => 'root',
        'password' => 'root',
      ),
    ),
    'default' => 'vidiun',
  ),
  'log' => 
  array (
    'ident' => 'vidiun',
    'level' => '7',
  ),
  'generator_version' => '1.4.2',
  'classmap' => 
  array (
    'VirusScanProfileTableMap' => 'lib/model/map/VirusScanProfileTableMap.php',
    'VirusScanProfilePeer' => 'lib/model/VirusScanProfilePeer.php',
    'VirusScanProfile' => 'lib/model/VirusScanProfile.php',
  ),
);
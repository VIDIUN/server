<?php
// This file generated by Propel  convert-conf target
// from XML runtime conf file /opt/vidiun/app/alpha/config/runtime-conf.xml
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
    'SphinxLogTableMap' => 'lib/model/map/SphinxLogTableMap.php',
    'SphinxLogPeer' => 'lib/model/SphinxLogPeer.php',
    'SphinxLog' => 'lib/model/SphinxLog.php',
    'SphinxLogServerTableMap' => 'lib/model/map/SphinxLogServerTableMap.php',
    'SphinxLogServerPeer' => 'lib/model/SphinxLogServerPeer.php',
    'SphinxLogServer' => 'lib/model/SphinxLogServer.php',
  ),
);
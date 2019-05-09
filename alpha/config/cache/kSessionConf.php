<?php

require_once __DIR__ . '/vBaseConfCache.php';
require_once __DIR__ . '/vMapCacheInterface.php';
require_once __DIR__ . '/vKeyCacheInterface.php';

class vSessionConf extends vBaseConfCache implements vMapCacheInterface , vKeyCacheInterface
{
	protected static $map = array();
	protected static $cacheKey=null;

	public function load($key, $mapName)
	{
		if(isset(self::$map[self::CONF_MAP_PREFIX.$mapName]))
			return self::$map[self::CONF_MAP_PREFIX.$mapName];
		return false;
	}

	public function hasMap($key, $mapName) { return isset(self::$map[self::CONF_MAP_PREFIX.$mapName]); }

	public function store($key, $mapName, $map, $ttl=0) { self::$map[self::CONF_MAP_PREFIX.$mapName] = $map; }

	public function deleteMap($key, $mapName) { unset(self::$map[self::CONF_MAP_PREFIX.$mapName]); }

	public function loadKey() { return self::$cacheKey; }

	public function storeKey($key, $ttl = 30) { self::$cacheKey = $key; }

	public function deleteKey() { self::$cacheKey = null; }
}

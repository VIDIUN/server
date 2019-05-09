<?php
require_once __DIR__ . '/vBaseConfCache.php';
require_once __DIR__ . '/vMapCacheInterface.php';

class vBaseMemcacheConf extends vBaseConfCache implements vMapCacheInterface
{
	protected $cache;
	protected $inLoad;

	protected function getCache()
	{
		return $this->cache;
	}

	function isActive()
	{
		return !is_null($this->cache);
	}

	function __construct()
	{
		$this->cache=null;
		$confParams = $this->getConfigParams(get_class($this));
		if($confParams)
		{
			$port = $confParams['port'];
			$host = $confParams['host'];
			$this->cache = $this->initCache($port, $host);
		}
	}

	protected function getConfigParams($mapName)
	{
		$map = vConfCacheManager::load($mapName,$mapName);
		return $map;
	}

	protected function initCache($port, $host)
	{
		require_once (__DIR__ . '/../../../infra/cache/vInfraMemcacheCacheWrapper.php');
		$cache = new vInfraMemcacheCacheWrapper;
		$sectionConfig= array('host'=>$host,'port'=>$port);
		try
		{
			if (!$cache->init($sectionConfig))
				$cache = null;
		}
		catch (Exception $e)
		{
			$cache=null;
		}
		return $cache;
	}

	public function load($key, $mapName)
	{
		$cache = $this->getCache();
		if($cache)
			return $cache->get(self::CONF_MAP_PREFIX.$mapName);
		return null;
	}

	public function store($key, $mapName, $map, $ttl = 0)
	{
		$cache = $this->getCache();
		if($cache)
			return $cache->set(self::CONF_MAP_PREFIX.$mapName, $map); // try to fetch from cache
		return null;
	}

	public function delete($key, $mapName)
	{
		$cache = $this->getCache();
		if($cache)
			return $cache->delete(self::CONF_MAP_PREFIX.$mapName);
		return false;
	}
}

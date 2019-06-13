<?php
require_once __DIR__ . '/vBaseConfCache.php';
require_once __DIR__ . '/vMapCacheInterface.php';
require_once __DIR__ . '/vKeyCacheInterface.php';

class vApcConf extends vBaseConfCache implements vMapCacheInterface , vKeyCacheInterface
{
	protected $reloadFileExist;
	protected $apcFunctionsExist;

	public function __construct()
	{
		$reloadFile = vEnvironment::get('cache_root_path').'base.reload';
		$this->apcFunctionsExist = function_exists('apc_fetch');
		$this->reloadFileExist = file_exists($reloadFile);
		if($this->reloadFileExist)
		{
			$deleted = @unlink($reloadFile);
			error_log('Base configuration reloaded');
			if(!$deleted)
				error_log('Failed to delete base.reload file');
		}

		parent::__construct();
	}

	public function delete($mapName)
	{
		if($this->apcFunctionsExist)
			return apc_delete(self::CONF_MAP_PREFIX.$mapName);
	}

	public function load($key, $mapName)
	{
		if($this->apcFunctionsExist && !$this->reloadFileExist)
		{
			$mapStr = apc_fetch(self::CONF_MAP_PREFIX.$mapName);
			$map = json_decode($mapStr,true);
			if ($map && $this->validateMap($map, $mapName, $key))
			{
				$this->removeKeyFromMap($map);
				return $map;
			}
		}
		return null;
	}

	public function store($key, $mapName, $map, $ttl=0)
	{
		if($this->apcFunctionsExist && PHP_SAPI != 'cli')
		{
			$this->addKeyToMap($map, $mapName, $key);
			$mapStr = json_encode($map);
			return apc_store(self::CONF_MAP_PREFIX.$mapName, $mapStr, $ttl);
		}
		return false;
	}

	public function loadKey()
	{
		if($this->apcFunctionsExist && !$this->reloadFileExist)
			return apc_fetch(vBaseConfCache::CONF_CACHE_VERSION_KEY);

		return null;
	}

	public function storeKey($key, $ttl=30)
	{
		if($this->apcFunctionsExist && PHP_SAPI != 'cli')
		{
			$existingKey = apc_fetch(vBaseConfCache::CONF_CACHE_VERSION_KEY);
			if(!$existingKey || strcmp($existingKey, $key))
			{
				return apc_store(vBaseConfCache::CONF_CACHE_VERSION_KEY, $key, $ttl);
			}
		}
		return null;
	}

	public function isKeyRequired()
	{
		return true;
	}
}

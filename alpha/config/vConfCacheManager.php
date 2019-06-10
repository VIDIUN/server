<?php
require_once (__DIR__ . "/cache/vCacheConfFactory.php");

class vConfCacheManager
{
	private static $mapLoadFlow	= array(vCacheConfFactory::SESSION,
										vCacheConfFactory::APC,
										vCacheConfFactory::LOCAL_MEM_CACHE,
										vCacheConfFactory::FILE_SYSTEM,
										vCacheConfFactory::REMOTE_MEM_CACHE);

	private static $mapStoreFlow = array(vCacheConfFactory::SESSION	=> array(),
										vCacheConfFactory::APC => array(vCacheConfFactory::SESSION),
										vCacheConfFactory::LOCAL_MEM_CACHE => array(vCacheConfFactory::APC, vCacheConfFactory::SESSION),
										vCacheConfFactory::FILE_SYSTEM => array(vCacheConfFactory::APC, vCacheConfFactory::SESSION),
										vCacheConfFactory::REMOTE_MEM_CACHE	=> array(vCacheConfFactory::APC, vCacheConfFactory::SESSION, vCacheConfFactory::LOCAL_MEM_CACHE));

	private static $keyLoadFlow	= array(vCacheConfFactory::SESSION,
										vCacheConfFactory::APC,
										vCacheConfFactory::REMOTE_MEM_CACHE);

	private static $keyStoreFlow = array(vCacheConfFactory::SESSION	=> array(),
										vCacheConfFactory::APC => array(vCacheConfFactory::SESSION),
										vCacheConfFactory::REMOTE_MEM_CACHE	=> array(vCacheConfFactory::APC, vCacheConfFactory::SESSION));

	private static $mapInitFlow = array(vCacheConfFactory::SESSION,
										vCacheConfFactory::APC,
										vCacheConfFactory::FILE_SYSTEM);

	private static $init=false;

	const KEY_TTL=30;
	const LONG_KEY_TTL=300;


	protected static function initLoad($cacheName)
	{
		foreach (self::$mapInitFlow as $cacheEntity)
		{
			/* @var $cacheObj vBaseConfCache*/
			$cacheObj = vCacheConfFactory::getInstance($cacheEntity);
			$map = $cacheObj->load(null, $cacheName);
			if($map)
			{
				self::store(null, $cacheName, $map, $cacheEntity);
				vCacheConfFactory::getInstance($cacheName);
				return;
			}
		}
	}


	protected static function init()
	{
		if(self::$init)
		{
			return;
		}
		self::$init=true;
		//load basic parameters
		//remote and local memcache	configuration maps
		self::initLoad(vCacheConfFactory::LOCAL_MEM_CACHE);
		self::initLoad(vCacheConfFactory::REMOTE_MEM_CACHE);
	}


	public static function getMap($mapName)
	{
		return self::load($mapName);
	}

	public static function loadKey()
	{
		self::init();

		foreach (self::$keyLoadFlow as $cacheEntity)
		{
			$cacheObj = vCacheConfFactory::getInstance($cacheEntity);
			$ret = $cacheObj->loadKey();
			if($ret)
			{
				$cacheObj->incKeyUsageCounter();
				self::storeKey($ret, $cacheEntity);
				return $ret;
			}
		}
		return null ; //no key is available
	}

	protected static function storeKey($key, $foundIn)
	{
		$remoteCache = vCacheConfFactory::getInstance(vCacheConfFactory::REMOTE_MEM_CACHE);
		$ttl=self::LONG_KEY_TTL;
		if($remoteCache->isActive())
		{
			$ttl=self::KEY_TTL;
		}

		$storeFlow = self::$keyStoreFlow[$foundIn];

		foreach ($storeFlow as $cacheEntity)
			vCacheConfFactory::getInstance($cacheEntity)->storeKey($key,$ttl);
	}

	public static function hasMap ($mapName)
	{
		$map = self::load($mapName);
		return !empty($map);
	}

	static $loadRecursiveLock;

	public static function load ($mapName, $key=null)
	{
		self::init();
		if(self::$loadRecursiveLock)
		{
			return array();
		}
		self::$loadRecursiveLock=true;

		foreach (self::$mapLoadFlow as $cacheEntity)
		{
			/* @var $cacheObj vBaseConfCache*/
			$cacheObj = vCacheConfFactory::getInstance($cacheEntity);
			if(!$key && $cacheObj->isKeyRequired() && PHP_SAPI != 'cli')
				$key = self::loadKey();

			$map = $cacheObj->load($key, $mapName);
			if($map)
			{
				$cacheObj->incUsage($mapName);
				self::store($key, $mapName, $map, $cacheEntity);
				self::$loadRecursiveLock=false;
				return $map;
			}
			$cacheObj->incCacheMissCounter();
		}
		vCacheConfFactory::getInstance(vCacheConfFactory::SESSION) -> store($key, $mapName,array());
		self::$loadRecursiveLock=false;
		return array();
	}

	static protected function store ($key, $mapName, $map, $foundIn)
	{
		$storeFlow = self::$mapStoreFlow[$foundIn];
		foreach ($storeFlow as $cacheEntity)
			vCacheConfFactory::getInstance($cacheEntity)->store($key, $mapName, $map);
	}

	static public function getUsage()
	{
		$out = array();
		foreach (self::$mapLoadFlow as $cacheEntity)
		{
			$out['usage'][$cacheEntity] = vCacheConfFactory::getInstance($cacheEntity)->getUsageCounter();
			$out['cacheMiss'][$cacheEntity] = vCacheConfFactory::getInstance($cacheEntity)->getCacheMissCounter();
		}
		foreach (self::$keyLoadFlow as $cacheEntity)
			$out['getKey'][$cacheEntity] = vCacheConfFactory::getInstance($cacheEntity)->getKeyUsageCounter();
		return $out;
	}

	static public function printUsage()
	{
		$str = "Conf usage:";
		foreach (self::$mapLoadFlow as $cacheEntity)
			$str .= $cacheEntity.'={'. vCacheConfFactory::getInstance($cacheEntity)->getUsageCounter().'}';
			$str .= '| Key usage: ';
		foreach (self::$keyLoadFlow as $cacheEntity)
			$str .= $cacheEntity.'={'. vCacheConfFactory::getInstance($cacheEntity)->getKeyUsageCounter().'}';
		$str .= '| Cache Miss: ';
		foreach (self::$mapLoadFlow as $cacheEntity)
			$str .= $cacheEntity.'={'. vCacheConfFactory::getInstance($cacheEntity)->getCacheMissCounter().'}';

			foreach (self::$mapLoadFlow as $cacheEntity)
		{
			$mapStr = vCacheConfFactory::getInstance($cacheEntity)->getUsageMap();
			$str .= "\n\r" . $cacheEntity . '=============>' . print_r($mapStr, true);
		}
		VidiunLog::debug($str);
	}
}
